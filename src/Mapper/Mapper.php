<?php
namespace Atlas\Orm\Mapper;

use Atlas\Orm\Exception;
use Atlas\Orm\Relation\ManyToMany;
use Atlas\Orm\Relation\ManyToOne;
use Atlas\Orm\Relation\OneToMany;
use Atlas\Orm\Relation\OneToOne;
use Atlas\Orm\Table\IdentityMap;
use Atlas\Orm\Table\Row;
use Atlas\Orm\Table\RowIdentity;
use Atlas\Orm\Table\RowSet;
use Atlas\Orm\Table\TableSelect;
use Atlas\Orm\Table\TableInterface;
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

    protected $relations;

    protected $events;

    protected $recordClass;

    public function __construct(
        ConnectionLocator $connectionLocator,
        QueryFactory $queryFactory,
        IdentityMap $identityMap,
        TableInterface $table,
        MapperEvents $events,
        MapperRelations $relations
    ) {
        $this->connectionLocator = $connectionLocator;
        $this->queryFactory = $queryFactory;
        $this->identityMap = $identityMap;
        $this->table = $table;
        $this->tableClass = get_class($this->table);
        $this->events = $events;
        $this->relations = $relations;
        $this->recordClass = substr(get_class($this), 0, -6) . 'Record';
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
        $row = $this->fetchRow($primaryVal);
        if (! $row) {
            return false;
        }
        return $this->newRecordFromRow($row, $with);
    }

    public function fetchRecordBy(array $colsVals = [], array $with = [])
    {
        $row = $this->fetchRowBy($colsVals);
        if (! $row) {
            return false;
        }
        return $this->newRecordFromRow($row, $with);
    }

    public function fetchRecordSet(array $primaryVals, array $with = array())
    {
        $rowSet = $this->fetchRowSet($primaryVals);
        if (! $rowSet) {
            return array();
        }
        return $this->newRecordSetFromRowSet($rowSet, $with);
    }

    public function fetchRecordSetBy(array $colsVals = [], array $with = array())
    {
        $rowSet = $this->fetchRowSetBy($colsVals);
        if (! $rowSet) {
            return array();
        }
        return $this->newRecordSetFromRowSet($rowSet, $with);
    }

    public function select(array $colsVals = [])
    {
        $tableSelect = $this->gatewaySelect($colsVals);
        return new MapperSelect(
            $tableSelect,
            [$this, 'newRecordFromRow'],
            [$this, 'newRecordSetFromRowSet']
        );
    }

    public function insert(Record $record)
    {
        $this->assertRecord($record);
        $this->events->beforeInsert($this, $record);
        return $this->gatewayInsert($record->getRow());
    }

    public function update(Record $record)
    {
        $this->assertRecord($record);
        $this->events->beforeUpdate($this, $record);
        return $this->gatewayUpdate($record->getRow());
    }

    public function delete(Record $record)
    {
        $this->assertRecord($record);
        $this->events->beforeDelete($this, $record);
        return $this->gatewayDelete($record->getRow());
    }

    public function newRecord(array $cols = [])
    {
        $row = $this->newRow($cols);
        $record = $this->newRecordFromRow($row);
        return $record;
    }

    public function newRecordFromRow(Row $row, array $with = [])
    {
        $recordClass = $this->recordClass;
        $record = new $recordClass($row, $this->newRelated());
        $this->relations->stitchIntoRecord($record, $with);
        return $record;
    }

    public function newRecordSet(array $records = [])
    {
        $recordSetClass = $this->recordClass . 'Set';
        return new $recordSetClass($records);
    }

    public function newRecordSetFromRowSet(RowSet $rowSet, array $with = [])
    {
        $records = [];
        foreach ($rowSet as $row) {
            $records[] = $this->newRecordFromRow($row);
        }
        $recordSet = $this->newRecordSet($records);
        $this->relations->stitchIntoRecordSet($recordSet, $with);
        return $recordSet;
    }

    protected function newRelated()
    {
        return new Related($this->relations->getFields());
    }

/** GATEWAY ***************************************************************** */

    protected function gatewaySelect(array $colsVals = [])
    {
        $select = new TableSelect(
            $this->queryFactory->newSelect(),
            $this->getReadConnection(),
            $this->table->getColNames(),
            [$this, 'newOrIdentifiedRow'],
            [$this, 'newOrIdentifiedRowSet']
        );
        $select->from($this->table->getName());
        foreach ($colsVals as $col => $val) {
            $this->gatewaySelectWhere($select, $col, $val);
        }
        return $select;
    }

    protected function gatewaySelectWhere(TableSelect $select, $col, $val)
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

    protected function fetchRow($primaryVal)
    {
        $primaryIdentity = $this->getPrimaryIdentity($primaryVal);
        $row = $this->identityMap->getRowByPrimary(
            $this->tableClass,
            $primaryIdentity
        );
        if (! $row) {
            $row = $this->gatewaySelect($primaryIdentity)->fetchRow();
        }
        return $row;
    }

    protected function fetchRowBy(array $colsVals)
    {
        return $this->gatewaySelect($colsVals)->fetchRow();
    }

    protected function fetchRowSet(array $primaryVals)
    {
        $rows = $this->identifyRows($primaryVals);
        if (! $rows) {
            return [];
        }

        return $this->newRowSet($rows);
    }

    protected function fetchRowSetBy(array $colsVals)
    {
        return $this->gatewaySelect($colsVals)->fetchRowSet();
    }

    /**
     *
     * Inserts a row.
     *
     * @param Row $row The row to insert.
     *
     * @return bool
     *
     */
    protected function gatewayInsert(Row $row)
    {
        $row->assertTableClass($this->tableClass);
        // $this->events->beforeInsert($this->table, $row);

        $insert = $this->newInsert($row);
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
            $row->$primary = $connection->lastInsertId($primary);
        }

        // $this->events->afterInsert($this->table, $row, $insert, $pdoStatement);
        $row->markAsSaved();

        // set into the identity map
        $this->identityMap->setRow($row, $row->getArrayCopy());
        return true;
    }

    /**
     *
     * Updates a row.
     *
     * @param Row $row The row to update.
     *
     * @return bool
     *
     */
    protected function gatewayUpdate(Row $row)
    {
        $row->assertTableClass($this->tableClass);
        // $this->events->beforeUpdate($this->table, $row);

        $update = $this->newUpdate($row);
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

        // $this->events->afterUpdate($this->table, $row, $update, $pdoStatement);
        $row->markAsSaved();

        // reinitialize the identity-map data for later updates
        $this->identityMap->setInitial($row);
        return true;
    }

    /**
     *
     * Deletes a row.
     *
     * @param Row $row The row to delete.
     *
     * @return bool
     *
     */
    protected function gatewayDelete(Row $row)
    {
        $row->assertTableClass($this->tableClass);
        // $this->events->beforeDelete($this->table, $row);

        $delete = $this->newDelete($row);
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

        // $this->events->afterDelete($this->table, $row, $delete, $pdoStatement);
        $row->markAsDeleted();

        return true;
    }

    protected function newRow(array $cols = [])
    {
        $cols = array_merge($this->table->getColDefaults(), $cols);
        $rowIdentity = $this->newRowIdentity($cols);
        $row = new Row($this->tableClass, $rowIdentity, $cols);
        return $row;
    }

    protected function newRowSet(array $rows)
    {
        return new RowSet($this->tableClass, $rows);
    }

    public function newOrIdentifiedRow(array $cols)
    {
        $primaryVal = $cols[$this->table->getPrimaryKey()];
        $primaryIdentity = $this->getPrimaryIdentity($primaryVal);
        $row = $this->identityMap->getRowByPrimary(
            $this->tableClass,
            $primaryIdentity
        );
        if (! $row) {
            $row = $this->newRow($cols);
            $row->markAsClean();
            $this->identityMap->setRow($row, $cols);
        }
        return $row;
    }

    public function newOrIdentifiedRowSet(array $data)
    {
        $rows = [];
        foreach ($data as $cols) {
            $rows[] = $this->newOrIdentifiedRow($cols);
        }
        return $this->newRowSet($rows);
    }

    protected function newInsert(Row $row)
    {
        $insert = $this->queryFactory->newInsert();
        $insert->into($this->table->getName());

        $cols = $row->getArrayCopy();
        if ($this->table->getAutoinc()) {
            unset($cols[$this->table->getPrimaryKey()]);
        }
        $insert->cols($cols);

        // $this->events->modifyInsert($this->table, $row, $insert);
        return $insert;
    }

    protected function newUpdate(Row $row)
    {
        $update = $this->queryFactory->newUpdate();
        $update->table($this->table->getName());

        $cols = $row->getArrayDiff($this->identityMap->getInitial($row));
        unset($cols[$this->table->getPrimaryKey()]);
        $update->cols($cols);

        $primaryCol = $this->table->getPrimaryKey();
        $update->where("{$primaryCol} = ?", $row->getIdentity()->getVal());

        // $this->events->modifyUpdate($this->table, $row, $update);
        return $update;
    }

    protected function newDelete(Row $row)
    {
        $delete = $this->queryFactory->newDelete();
        $delete->from($this->table->getName());

        $primaryCol = $this->table->getPrimaryKey();
        $delete->where("{$primaryCol} = ?", $row->getIdentity()->getVal());

        // $this->events->modifyDelete($this->table, $row, $delete);
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
    protected function identifyRows($primaryVals)
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
        $select = $this->gatewaySelect($colsVals);
        $data = $select->cols($this->table->getColNames())->fetchAll();
        foreach ($data as $cols) {
            $row = $this->newRow($cols);
            $this->identityMap->setRow($row, $cols);
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

    protected function assertRecord($record)
    {
        if (! is_object($record)) {
            throw Exception::invalidType($this->recordClass, gettype($record));
        }

        if (! $record instanceof $this->recordClass) {
            throw Exception::invalidType($this->recordClass, $record);
        }
    }
}
