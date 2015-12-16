<?php
namespace Atlas\Orm\Mapper;

use Atlas\Orm\Exception;
use Atlas\Orm\Relation\ManyToMany;
use Atlas\Orm\Relation\ManyToOne;
use Atlas\Orm\Relation\OneToMany;
use Atlas\Orm\Relation\OneToOne;
use Atlas\Orm\Table\Gateway;
use Atlas\Orm\Table\Row;
use Atlas\Orm\Table\RowSet;
use Atlas\Orm\Table\TableSelect;

/**
 *
 * A data source mapper that returns Record and RecordSet objects.
 *
 * @package Atlas.Atlas
 *
 */
class Mapper
{
    protected $gateway;

    protected $relations;

    protected $events;

    protected $recordClass;

    public function __construct(
        Gateway $gateway,
        MapperEvents $events,
        MapperRelations $relations
    ) {
        $this->gateway = $gateway;
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

    public function getGateway()
    {
        return $this->gateway;
    }

    public function getRelations()
    {
        return $this->relations;
    }

    public function fetchRecord($primaryVal, array $with = [])
    {
        $row = $this->gateway->fetchRow($primaryVal);
        if (! $row) {
            return false;
        }
        return $this->newRecordFromRow($row, $with);
    }

    public function fetchRecordBy(array $colsVals = [], array $with = [])
    {
        $row = $this->gateway->fetchRowBy($colsVals);
        if (! $row) {
            return false;
        }
        return $this->newRecordFromRow($row, $with);
    }

    public function fetchRecordSet(array $primaryVals, array $with = array())
    {
        $rowSet = $this->gateway->fetchRowSet($primaryVals);
        if (! $rowSet) {
            return array();
        }
        return $this->newRecordSetFromRowSet($rowSet, $with);
    }

    public function fetchRecordSetBy(array $colsVals = [], array $with = array())
    {
        $rowSet = $this->gateway->fetchRowSetBy($colsVals);
        if (! $rowSet) {
            return array();
        }
        return $this->newRecordSetFromRowSet($rowSet, $with);
    }

    public function select(array $colsVals = [])
    {
        $tableSelect = $this->gateway->select($colsVals);
        return $this->newMapperSelect($tableSelect);
    }

    public function insert(Record $record)
    {
        $this->assertRecord($record);
        $this->events->beforeInsert($this, $record);
        return $this->gateway->insert($record->getRow());
    }

    public function update(Record $record)
    {
        $this->assertRecord($record);
        $this->events->beforeUpdate($this, $record);
        return $this->gateway->update($record->getRow());
    }

    public function delete(Record $record)
    {
        $this->assertRecord($record);
        $this->events->beforeDelete($this, $record);
        return $this->gateway->delete($record->getRow());
    }

    public function newRecord(array $cols = [])
    {
        $row = $this->gateway->newRow($cols);
        $record = $this->newRecordFromRow($row);
        $this->events->modifyNewRecord($this, $record);
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

    protected function newMapperSelect(TableSelect $tableSelect)
    {
        return new MapperSelect(
            $tableSelect,
            [$this, 'newRecordFromRow'],
            [$this, 'newRecordSetFromRowSet']
        );
    }

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
