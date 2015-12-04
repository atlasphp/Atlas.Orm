<?php
namespace Atlas\Orm\Mapper;

use Atlas\Orm\Exception;
use Atlas\Orm\Table\Row;
use Atlas\Orm\Table\RowSet;
use Atlas\Orm\Table\Table;
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
    protected $table;

    protected $relations;

    protected $recordFactory;

    protected $mapperEvents;

    protected $recordClass;

    public function __construct(
        Table $table,
        RecordFactory $recordFactory,
        MapperEvents $mapperEvents,
        MapperRelations $relations
    ) {
        $this->table = $table;
        $this->recordFactory = $recordFactory;
        $this->mapperEvents = $mapperEvents;
        $this->relations = $relations;
        $this->recordClass = substr(get_class($this), 0, -6) . 'Record';
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

    public function getRelations()
    {
        return $this->relations;
    }

    public function newRecord(array $cols = [])
    {
        $row = $this->getTable()->newRow($cols);
        return $this->recordFactory->newRecordFromRow($row, $this->relations->getFields());
    }

    public function newRecordSet(array $records = [])
    {
        return $this->recordFactory->newRecordSet($records);
    }

    public function fetchRecord($primaryVal, array $with = [])
    {
        $row = $this->table->fetchRow($primaryVal);
        if (! $row) {
            return false;
        }
        return $this->newRecordFromRow($row, $with);
    }

    public function fetchRecordBy(array $colsVals = [], array $with = [])
    {
        $row = $this->table->fetchRowBy($colsVals);
        if (! $row) {
            return false;
        }
        return $this->newRecordFromRow($row, $with);
    }

    public function newRecordFromRow(Row $row, array $with = [])
    {
        $record = $this->recordFactory->newRecordFromRow($row, $this->relations->getFields());
        $this->relations->stitchIntoRecord($record, $with);
        return $record;
    }

    public function fetchRecordSet(array $primaryVals, array $with = array())
    {
        $rowSet = $this->table->fetchRowSet($primaryVals);
        if (! $rowSet) {
            return array();
        }
        return $this->newRecordSetFromRowSet($rowSet, $with);
    }

    public function fetchRecordSetBy(array $colsVals = [], array $with = array())
    {
        $rowSet = $this->table->fetchRowSetBy($colsVals);
        if (! $rowSet) {
            return array();
        }
        return $this->newRecordSetFromRowSet($rowSet, $with);
    }

    public function newRecordSetFromRowSet(RowSet $rowSet, array $with = [])
    {
        $recordSet = $this->recordFactory->newRecordSetFromRowSet($rowSet, $this->relations->getFields());
        $this->relations->stitchIntoRecordSet($recordSet, $with);
        return $recordSet;
    }

    protected function newMapperSelect(TableSelect $tableSelect)
    {
        return new MapperSelect(
            $tableSelect,
            [$this, 'newRecordFromRow'],
            [$this, 'newRecordSetFromRowSet']
        );
    }

    public function select(array $colsVals = [])
    {
        $tableSelect = $this->getTable()->select($colsVals);
        return $this->newMapperSelect($tableSelect);
    }

    public function insert(Record $record)
    {
        $this->assertRecord($record);
        $this->mapperEvents->beforeInsert($this, $record);
        return $this->getTable()->insert($record->getRow());
    }

    public function update(Record $record)
    {
        $this->assertRecord($record);
        $this->mapperEvents->beforeUpdate($this, $record);
        return $this->getTable()->update($record->getRow());
    }

    public function delete(Record $record)
    {
        $this->assertRecord($record);
        $this->mapperEvents->beforeDelete($this, $record);
        return $this->getTable()->delete($record->getRow());
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
