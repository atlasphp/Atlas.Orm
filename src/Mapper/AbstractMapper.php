<?php
namespace Atlas\Orm\Mapper;

use Atlas\Orm\Exception;
use Atlas\Orm\Relationship\ManyToMany;
use Atlas\Orm\Relationship\ManyToOne;
use Atlas\Orm\Relationship\OneToMany;
use Atlas\Orm\Relationship\OneToOne;
use Atlas\Orm\Relationship\Relationships;
use Atlas\Orm\Table\IdentityMap;
use Atlas\Orm\Table\RowInterface;
use Atlas\Orm\Table\Gateway;
use Aura\Sql\ConnectionLocator;
use Aura\SqlQuery\QueryFactory;

/**
 *
 * A data source mapper that returns Record and RecordSet objects.
 *
 * @package Atlas.Atlas
 *
 */
abstract class AbstractMapper implements MapperInterface
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

    protected $gateway;

    protected $mapperClass;

    protected $relationships;

    protected $plugin;

    public function __construct(
        ConnectionLocator $connectionLocator,
        Gateway $gateway,
        PluginInterface $plugin,
        Relationships $relationships
    ) {
        $this->connectionLocator = $connectionLocator;
        $this->gateway = $gateway;
        $this->plugin = $plugin;
        $this->relationships = $relationships;

        $this->mapperClass = get_class($this);
        $this->table = $this->gateway->getTable();

        $this->setRelated();
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

    public function fetchRecord($primaryVal, array $with = [])
    {
        $row = $this->gateway->selectRowByPrimary($this->select(), $primaryVal);
        if (! $row) {
            return false;
        }
        return $this->newRecordFromRow($row, $with);
    }

    public function fetchRecordBy(array $colsVals = [], array $with = [])
    {
        $row = $this->gateway->selectRow($this->select($colsVals));
        if (! $row) {
            return false;
        }
        return $this->newRecordFromRow($row, $with);
    }

    public function fetchRecordSet(array $primaryVals, array $with = [])
    {
        $rows = $this->gateway->selectRowsByPrimary($this->select(), $primaryVals);
        if (! $rows) {
            return [];
        }
        return $this->newRecordSetFromRows($rows, $with);
    }

    public function fetchRecordSetBy(array $colsVals = [], array $with = [])
    {
        $rows = $this->gateway->selectRows($this->select($colsVals));
        if (! $rows) {
            return [];
        }
        return $this->newRecordSetFromRows($rows, $with);
    }

    public function select(array $colsVals = [])
    {
        return new Select(
            $this->gateway->newSelect($colsVals),
            $this->getReadConnection(),
            $this->table->getColNames(),
            [$this, 'getSelectedRecord'],
            [$this, 'getSelectedRecordSet']
        );
    }

    /**
     *
     * Inserts the Row for a Record.
     *
     * @param RecordInterface $record Insert the Row for this Record.
     *
     * @return bool
     *
     */
    public function insert(RecordInterface $record)
    {
        $this->plugin->beforeInsert($this, $record);

        $row = $record->getRow();
        $insert = $this->gateway->newInsert(
            $row,
            [$this->plugin, 'modifyInsert']
        );

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
        $this->gateway->inserted($row);
        return true;
    }

    /**
     *
     * Updates the Row for a Record.
     *
     * @param RecordInterface $record Update the Row for this Record.
     *
     * @return bool
     *
     */
    public function update(RecordInterface $record)
    {
        $this->plugin->beforeUpdate($this, $record);

        $row = $record->getRow();

        $update = $this->gateway->newUpdate(
            $row,
            [$this->plugin, 'modifyUpdate']
        );

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
        $this->gateway->updated($row);
        return true;
    }

    /**
     *
     * Deletes the Row for a Record.
     *
     * @param RecordInterface $record Delete the Row for this Record.
     *
     * @return bool
     *
     */
    public function delete(RecordInterface $record)
    {
        $this->plugin->beforeDelete($this, $record);

        $row = $record->getRow();
        $delete = $this->gateway->newDelete(
            $row,
            [$this->plugin, 'modifyDelete']
        );

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
        $this->gateway->deleted($row);
        return true;
    }

    public function newRecord(array $cols = [])
    {
        $row = $this->gateway->newRow($cols);
        $record = $this->newRecordFromRow($row);
        $this->plugin->modifyNewRecord($record);
        return $record;
    }

    public function getSelectedRecord(array $cols, array $with = [])
    {
        $row = $this->gateway->getIdentifiedOrSelectedRow($cols);
        return $this->newRecordFromRow($row, $with);
    }

    protected function getRecordClass(RowInterface $row)
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

    protected function newRecordFromRow(RowInterface $row, array $with = [])
    {
        $recordClass = $this->getRecordClass($row);
        $record = new $recordClass(
            $this->mapperClass,
            $row,
            $this->newRelated()
        );
        $this->relationships->stitchIntoRecord($record, $with);
        return $record;
    }

    protected function newRelated()
    {
        return new Related($this->relationships->getFields());
    }

    public function newRecordSet(array $records = [], array $with = [])
    {
        $recordSetClass = $this->getRecordSetClass();
        $recordSet = new $recordSetClass($records);
        $this->relationships->stitchIntoRecordSet($recordSet, $with);
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
        $this->relationships->stitchIntoRecordSet($recordSet, $with);
        return $recordSet;
    }

    protected function setRelated()
    {
    }

    protected function oneToOne($name, $foreignMapperClass)
    {
        return $this->relationships->set(
            get_class($this),
            $name,
            OneToOne::CLASS,
            $foreignMapperClass
        );
    }

    protected function oneToMany($name, $foreignMapperClass)
    {
        return $this->relationships->set(
            get_class($this),
            $name,
            OneToMany::CLASS,
            $foreignMapperClass
        );
    }

    protected function manyToOne($name, $foreignMapperClass)
    {
        return $this->relationships->set(
            get_class($this),
            $name,
            ManyToOne::CLASS,
            $foreignMapperClass
        );
    }

    protected function manyToMany($name, $foreignMapperClass, $throughName)
    {
        return $this->relationships->set(
            get_class($this),
            $name,
            ManyToMany::CLASS,
            $foreignMapperClass,
            $throughName
        );
    }
}
