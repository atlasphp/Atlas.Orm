<?php
namespace Atlas\Orm\Mapper;

use Atlas\Orm\Exception;
use Atlas\Orm\Relation\ManyToMany;
use Atlas\Orm\Relation\ManyToOne;
use Atlas\Orm\Relation\OneToMany;
use Atlas\Orm\Relation\OneToOne;
use Atlas\Orm\Mapper\IdentityMap;
use Atlas\Orm\Mapper\Row;
use Atlas\Orm\Mapper\RowIdentity;
use Atlas\Orm\Mapper\TableInterface;
use Aura\Sql\ConnectionLocator;
use Aura\SqlQuery\QueryFactory;

/**
 *
 * A data source mapper that returns Record and RecordSet objects.
 *
 * @package Atlas.Atlas
 *
 */
class Mapper
{
    /**
     *
     * A database connection locator.
     *
     * @var ConnectionLocator
     *
     */
    protected $connectionLocator;

    /**
     *
     * A factory to create query statements.
     *
     * @var QueryFactory
     *
     */
    protected $queryFactory;

    /**
     *
     * A read connection drawn from the connection locator.
     *
     * @var ExtendedPdoInterface
     *
     */
    protected $readConnection;

    /**
     *
     * A write connection drawn from the connection locator.
     *
     * @var ExtendedPdoInterface
     *
     */
    protected $writeConnection;

    protected $table;

    protected $identityMap;

    protected $tableClass;

    protected $mapperClass;

    protected $relations;

    protected $plugin;

    public function __construct(
        ConnectionLocator $connectionLocator,
        QueryFactory $queryFactory,
        IdentityMap $identityMap,
        TableInterface $table,
        PluginInterface $plugin,
        Relations $relations
    ) {
        $this->connectionLocator = $connectionLocator;
        $this->queryFactory = $queryFactory;
        $this->identityMap = $identityMap;
        $this->table = $table;
        $this->plugin = $plugin;
        $this->relations = $relations;

        $this->tableClass = get_class($this->table);
        $this->mapperClass = get_class($this);

        $this->defineRelations();
    }

    static public function getTableClass()
    {
        static $tableClass;
        if (! $tableClass) {
            $tableClass = substr(get_called_class(), 0, -6) . 'Table';
        }
        return $tableClass;
    }

    public function getTable()
    {
        return $this->table;
    }

    /**
     *
     * Returns the database read connection.
     *
     * @return ExtendedPdoInterface
     *
     */
    public function getReadConnection()
    {
        if (! $this->readConnection) {
            $this->readConnection = $this->connectionLocator->getRead();
        }
        return $this->readConnection;
    }

    /**
     *
     * Returns the database write connection.
     *
     * @return ExtendedPdoInterface
     *
     */
    public function getWriteConnection()
    {
        if (! $this->writeConnection) {
            $this->writeConnection = $this->connectionLocator->getWrite();
        }
        return $this->writeConnection;
    }

    public function getIdentityMap()
    {
        return $this->identityMap;
    }

    public function getRelations()
    {
        return $this->relations;
    }

    public function fetchRecord($primaryVal, array $with = [])
    {
        $primaryIdentity = $this->getPrimaryIdentity($primaryVal);

        $row = $this->identityMap->getRowByPrimary(
            $this->tableClass,
            $primaryIdentity
        );

        if ($row) {
            return $this->newRecordFromRow($row, $with);
        }

        return $this->fetchRecordBy($primaryIdentity, $with);
    }

    public function fetchRecordBy(array $colsVals = [], array $with = [])
    {
        $cols = $this
            ->select($colsVals)
            ->cols($this->table->getColNames())
            ->fetchOne();

        if (! $cols) {
            return false;
        }

        $row = $this->getIdentifiedOrSelectedRow($cols);
        return $this->newRecordFromRow($row, $with);
    }

    public function fetchRecordSet(array $primaryVals, array $with = [])
    {
        $rows = $this->identifyOrFetchRows($primaryVals);
        if (! $rows) {
            return [];
        }
        return $this->newRecordSetFromRows($rows, $with);
    }

    public function fetchRecordSetBy(array $colsVals = [], array $with = [])
    {
        $data = $this
            ->select($colsVals)
            ->cols($this->table->getColNames())
            ->fetchAll();

        if (! $data) {
            return [];
        }

        $rows = [];
        foreach ($data as $cols) {
            $rows[] = $this->getIdentifiedOrSelectedRow($cols);
        }

        return $this->newRecordSetFromRows($rows, $with);
    }

    public function select(array $colsVals = [])
    {
        $select = new Select(
            $this->queryFactory->newSelect(),
            $this->getReadConnection(),
            $this->table->getColNames(),
            [$this, 'getSelectedRecord'],
            [$this, 'getSelectedRecordSet']
        );

        $select->from($this->table->getName());

        foreach ($colsVals as $col => $val) {
            $this->selectWhere($select, $col, $val);
        }

        return $select;
    }

    protected function selectWhere(Select $select, $col, $val)
    {
        $col = $this->table->getName() . '.' . $col;

        if (is_array($val)) {
            return $select->where("{$col} IN (?)", $val);
        }

        if ($val === null) {
            return $select->where("{$col} IS NULL");
        }

        $select->where("{$col} = ?", $val);
    }

    /**
     *
     * Inserts the Row for a Record.
     *
     * @param Record $record Insert the Row for this Record.
     *
     * @return bool
     *
     */
    public function insert(Record $record)
    {
        $this->plugin->beforeInsert($this, $record);

        $insert = $this->newInsert($record);
        $connection = $this->getWriteConnection();
        $pdoStatement = $connection->perform(
            $insert->getStatement(),
            $insert->getBindValues()
        );

        if (! $pdoStatement->rowCount()) {
            throw Exception::unexpectedRowCountAffected(0);
        }

        if ($this->table->getAutoinc()) {
            $primary = $this->table->getPrimaryKey();
            $record->$primary = $connection->lastInsertId($primary);
        }

        $this->plugin->afterInsert($this, $record, $insert, $pdoStatement);

        // mark as saved and retain in identity map
        $row = $record->getRow();
        $row->markAsSaved();
        $this->identityMap->setRow($row, $row->getArrayCopy());
        return true;
    }

    /**
     *
     * Updates the Row for a Record.
     *
     * @param Record $record Update the Row for this Record.
     *
     * @return bool
     *
     */
    public function update(Record $record)
    {
        $this->plugin->beforeUpdate($this, $record);

        $update = $this->newUpdate($record);
        if (! $update->hasCols()) {
            return false;
        }

        $connection = $this->getWriteConnection();
        $pdoStatement = $connection->perform(
            $update->getStatement(),
            $update->getBindValues()
        );

        $rowCount = $pdoStatement->rowCount();
        if ($rowCount != 1) {
            throw Exception::unexpectedRowCountAffected($rowCount);
        }

        $this->plugin->afterUpdate($this, $record, $update, $pdoStatement);

        // mark as saved and retain updated identity-map data
        $row = $record->getRow();
        $row->markAsSaved();
        $this->identityMap->setInitial($row);
        return true;
    }

    /**
     *
     * Deletes the Row for a Record.
     *
     * @param Record $record Delete the Row for this Record.
     *
     * @return bool
     *
     */
    public function delete(Record $record)
    {
        $this->plugin->beforeDelete($this, $record);

        $delete = $this->newDelete($record);
        $connection = $this->getWriteConnection();
        $pdoStatement = $connection->perform(
            $delete->getStatement(),
            $delete->getBindValues()
        );

        $rowCount = $pdoStatement->rowCount();
        if (! $rowCount) {
            return false;
        }

        if ($rowCount != 1) {
            throw Exception::unexpectedRowCountAffected($rowCount);
        }

        $this->plugin->afterDelete($this, $record, $delete, $pdoStatement);

        // mark as deleted, no need to update identity map
        $row = $record->getRow();
        $row->markAsDeleted();
        return true;
    }

    public function newRecord(array $cols = [])
    {
        $row = $this->newRow($cols);
        $record = $this->newRecordFromRow($row);
        $this->plugin->modifyNewRecord($record);
        return $record;
    }

    public function getSelectedRecord(array $cols, array $with = [])
    {
        $row = $this->getIdentifiedOrSelectedRow($cols);
        return $this->newRecordFromRow($row, $with);
    }

    protected function getRecordClass(Row $row)
    {
        static $recordClass;
        if (! $recordClass) {
            $recordClass = substr(get_class($this), 0, -6) . 'Record';
            $recordClass = class_exists($recordClass)
                ? $recordClass
                : Record::CLASS;
        }
        return $recordClass;
    }

    protected function getRecordSetClass()
    {
        static $recordSetClass;
        if (! $recordSetClass) {
            $recordSetClass = substr(get_class($this), 0, -6) . 'RecordSet';
            $recordSetClass = class_exists($recordSetClass)
                ? $recordSetClass
                : RecordSet::CLASS;
        }
        return $recordSetClass;
    }

    protected function newRecordFromRow(Row $row, array $with = [])
    {
        $recordClass = $this->getRecordClass($row);
        $record = new $recordClass($this->mapperClass, $row, $this->newRelated());
        $this->relations->stitchIntoRecord($record, $with);
        return $record;
    }

    public function newRecordSet(array $records = [], array $with = [])
    {
        $recordSetClass = $this->getRecordSetClass();
        $recordSet = new $recordSetClass($records);
        $this->relations->stitchIntoRecordSet($recordSet, $with);
        return $recordSet;
    }

    protected function newRecordSetFromRows(array $rows, array $with = [])
    {
        $records = [];
        foreach ($rows as $row) {
            $records[] = $this->newRecordFromRow($row);
        }
        return $this->newRecordSet($records, $with);
    }

    public function getSelectedRecordSet(array $data, array $with = [])
    {
        $records = [];
        foreach ($data as $cols) {
            $records[] = $this->getSelectedRecord($cols);
        }
        $recordSet = $this->newRecordSet($records);
        $this->relations->stitchIntoRecordSet($recordSet, $with);
        return $recordSet;
    }

    protected function newRelated()
    {
        return new Related($this->relations->getFields());
    }

    protected function newRow(array $cols = [])
    {
        $cols = array_merge($this->table->getColDefaults(), $cols);
        $rowIdentity = $this->newRowIdentity($cols);
        $row = new Row($this->tableClass, $rowIdentity, $cols);
        return $row;
    }

    protected function newSelectedRow(array $cols)
    {
        $row = $this->newRow($cols);
        $row->markAsClean();
        $this->identityMap->setRow($row, $cols);
        return $row;
    }

    protected function getIdentifiedOrSelectedRow(array $cols)
    {
        $primaryVal = $cols[$this->table->getPrimaryKey()];
        $primaryIdentity = $this->getPrimaryIdentity($primaryVal);
        $row = $this->identityMap->getRowByPrimary(
            $this->tableClass,
            $primaryIdentity
        );
        if (! $row) {
            $row = $this->newSelectedRow($cols);
        }
        return $row;
    }

    protected function newInsert(Record $record)
    {
        $insert = $this->queryFactory->newInsert();
        $insert->into($this->table->getName());

        $row = $record->getRow();
        $cols = $row->getArrayCopy();
        if ($this->table->getAutoinc()) {
            unset($cols[$this->table->getPrimaryKey()]);
        }
        $insert->cols($cols);

        $this->plugin->modifyInsert($this, $record, $insert);
        return $insert;
    }

    protected function newUpdate(Record $record)
    {
        $update = $this->queryFactory->newUpdate();
        $update->table($this->table->getName());

        $row = $record->getRow();
        $cols = $row->getArrayDiff($this->identityMap->getInitial($row));
        unset($cols[$this->table->getPrimaryKey()]);
        $update->cols($cols);

        $primaryCol = $this->table->getPrimaryKey();
        $update->where("{$primaryCol} = ?", $row->getIdentity()->getVal());

        $this->plugin->modifyUpdate($this, $record, $update);
        return $update;
    }

    protected function newDelete(Record $record)
    {
        $delete = $this->queryFactory->newDelete();
        $delete->from($this->table->getName());

        $row = $record->getRow();
        $primaryCol = $this->table->getPrimaryKey();
        $delete->where("{$primaryCol} = ?", $row->getIdentity()->getVal());

        $this->plugin->modifyDelete($this, $record, $delete);
        return $delete;
    }

    protected function newRowIdentity(array &$cols)
    {
        $primaryCol = $this->table->getPrimaryKey();
        $primaryVal = null;
        if (array_key_exists($primaryCol, $cols)) {
            $primaryVal = $cols[$primaryCol];
            unset($cols[$primaryCol]);
        }
        return new RowIdentity([$primaryCol => $primaryVal]);
    }

    protected function getPrimaryIdentity($primaryVal)
    {
        return [$this->table->getPrimaryKey() => $primaryVal];
    }

    /*
    Retrieve rows from identity map and/or database.

    Rows by primary:
        create empty rows
        foreach primary value ...
            add null in rows keyed on primary value to maintain place
            if primary value in map
                retain mapped row in set keyed on primary value
                remove primary value from list
        select remaining primary values
        foreach returned one ...
            new row object
            retain row in map
            add row in set on ID key
        return rows
    */
    protected function identifyOrFetchRows($primaryVals)
    {
        if (! $primaryVals) {
            return [];
        }

        $rows = [];
        foreach ($primaryVals as $i => $primaryVal) {
            $rows[$primaryVal] = null;
            $primaryIdentity = $this->getPrimaryIdentity($primaryVal);
            $hasPrimary = $this->identityMap->hasPrimary(
                $this->tableClass,
                $primaryIdentity
            );
            if ($hasPrimary) {
                $rows[$primaryVal] = $this->identityMap->getRowByPrimary(
                    $this->tableClass,
                    $primaryIdentity
                );
                unset($primaryVals[$i]);
            }
        }

        // are there still rows to fetch?
        if (! $primaryVals) {
            return array_values($rows);
        }

        // fetch and retain remaining rows
        $colsVals = [$this->table->getPrimaryKey() => $primaryVals];
        $select = $this->select($colsVals);
        $data = $select->cols($this->table->getColNames())->fetchAll();
        foreach ($data as $cols) {
            $row = $this->newSelectedRow($cols);
            $rows[$row->getIdentity()->getVal()] = $row;
        }

        // remove unfound rows
        foreach ($rows as $key => $val) {
            if ($val === null) {
                unset($rows[$key]);
            }
        }

        // done
        return array_values($rows);
    }

/** RECORD SUPPORT ********************************************************** */

    protected function defineRelations()
    {
    }

    protected function oneToOne($name, $foreignMapperClass)
    {
        return $this->relations->set(
            get_class($this),
            $name,
            OneToOne::CLASS,
            $foreignMapperClass
        );
    }

    protected function oneToMany($name, $foreignMapperClass)
    {
        return $this->relations->set(
            get_class($this),
            $name,
            OneToMany::CLASS,
            $foreignMapperClass
        );
    }

    protected function manyToOne($name, $foreignMapperClass)
    {
        return $this->relations->set(
            get_class($this),
            $name,
            ManyToOne::CLASS,
            $foreignMapperClass
        );
    }

    protected function manyToMany($name, $foreignMapperClass, $throughName)
    {
        return $this->relations->set(
            get_class($this),
            $name,
            ManyToMany::CLASS,
            $foreignMapperClass,
            $throughName
        );
    }
}
