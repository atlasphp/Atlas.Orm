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
use Atlas\Orm\Table\GatewayInterface;
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
    protected $gateway;

    protected $relationships;

    protected $plugin;

    public function __construct(
        GatewayInterface $gateway,
        PluginInterface $plugin,
        Relationships $relationships
    ) {
        $this->gateway = $gateway;
        $this->plugin = $plugin;
        $this->relationships = $relationships;
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
        return $this->gateway->getTable();
    }

    public function getGateway()
    {
        return $this->gateway;
    }

    /**
     *
     * Returns the Gateway read connection.
     *
     * @return ExtendedPdoInterface
     *
     */
    public function getReadConnection()
    {
        return $this->gateway->getReadConnection();
    }

    /**
     *
     * Returns the Gateway write connection.
     *
     * @return ExtendedPdoInterface
     *
     */
    public function getWriteConnection()
    {
        return $this->gateway->getWriteConnection();
    }

    public function fetchRecord($primaryVal, array $with = [])
    {
        $row = $this->gateway->fetchRow($primaryVal);
        if (! $row) {
            return false;
        }
        return $this->newRecordFromRow($row, $with);
    }

    public function fetchRecordSet(array $primaryVals, array $with = [])
    {
        $rows = $this->gateway->fetchRows($primaryVals);
        if (! $rows) {
            return [];
        }
        return $this->newRecordSetFromRows($rows, $with);
    }

    public function fetchRecordBy(array $colsVals = [], array $with = [])
    {
        $row = $this->gateway->selectRow($this->gateway->select($colsVals));
        if (! $row) {
            return false;
        }
        return $this->newRecordFromRow($row, $with);
    }

    public function fetchRecordSetBy(array $colsVals = [], array $with = [])
    {
        $rows = $this->gateway->selectRows($this->gateway->select($colsVals));
        if (! $rows) {
            return [];
        }
        return $this->newRecordSetFromRows($rows, $with);
    }

    public function select(array $colsVals = [])
    {
        return new MapperSelect(
            $this->gateway->select($colsVals),
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
        return $this->gateway->insert(
            $record->getRow(),
            [$this->plugin, 'modifyInsert'],
            [$this->plugin, 'afterInsert']
        );
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
        return $this->gateway->update(
            $record->getRow(),
            [$this->plugin, 'modifyUpdate'],
            [$this->plugin, 'afterUpdate']
        );
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
        return $this->gateway->delete(
            $record->getRow(),
            [$this->plugin, 'modifyDelete'],
            [$this->plugin, 'afterDelete']
        );
    }

    public function newRecord(array $cols = [])
    {
        $row = $this->gateway->newRow($cols);
        return $this->newRecordFromRow($row);
    }

    public function newRecordSet(array $records = [], array $with = [])
    {
        $recordSetClass = $this->getRecordSetClass();
        $recordSet = new $recordSetClass($records);
        $this->relationships->stitchIntoRecordSet($recordSet, $with);
        return $recordSet;
    }

    public function getSelectedRecord(array $cols, array $with = [])
    {
        $row = $this->gateway->getSelectedRow($cols);
        return $this->newRecordFromRow($row, $with);
    }

    public function getSelectedRecordSet(array $data, array $with = [])
    {
        $records = [];
        foreach ($data as $cols) {
            $records[] = $this->getSelectedRecord($cols);
        }
        return $this->newRecordSet($records, $with);
    }

    protected function setRelated()
    {
    }

    protected function oneToOne($name, $foreignMapperClass)
    {
        return $this->relationships->set(
            static::CLASS,
            $name,
            OneToOne::CLASS,
            $foreignMapperClass
        );
    }

    protected function oneToMany($name, $foreignMapperClass)
    {
        return $this->relationships->set(
            static::CLASS,
            $name,
            OneToMany::CLASS,
            $foreignMapperClass
        );
    }

    protected function manyToOne($name, $foreignMapperClass)
    {
        return $this->relationships->set(
            static::CLASS,
            $name,
            ManyToOne::CLASS,
            $foreignMapperClass
        );
    }

    protected function manyToMany($name, $foreignMapperClass, $throughName)
    {
        return $this->relationships->set(
            static::CLASS,
            $name,
            ManyToMany::CLASS,
            $foreignMapperClass,
            $throughName
        );
    }

    protected function getRecordClass(RowInterface $row)
    {
        static $recordClass;
        if (! $recordClass) {
            $recordClass = substr(static::CLASS, 0, -6) . 'Record';
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
            $recordSetClass = substr(static::CLASS, 0, -6) . 'RecordSet';
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
            static::CLASS,
            $row,
            $this->newRelated()
        );
        $this->relationships->stitchIntoRecord($record, $with);
        return $record;
    }

    protected function newRecordSetFromRows(array $rows, array $with = [])
    {
        $records = [];
        foreach ($rows as $row) {
            $records[] = $this->newRecordFromRow($row);
        }
        return $this->newRecordSet($records, $with);
    }

    protected function newRelated()
    {
        return new Related($this->relationships->getFields());
    }
}
