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

    public function newRecord(array $data = [])
    {
        $row = $this->getTable()->newRow($data);
        return $this->newRecordFromRow($row);
    }

    public function newRecordFromRow(Row $row)
    {
        return $this->recordFactory->newRecord($row, $this->relations->getFields());
    }

    // rowSet can be array of Rows, or RowSet object
    public function newRecordSetFromRows($rows)
    {
        return $this->recordFactory->newRecordSetFromRows($rows, $this->relations->getFields());
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
        return $this->convertRow($row, $with);
    }

    public function fetchRecordBy(array $colsVals = [], array $with = [])
    {
        $row = $this->table->fetchRowBy($colsVals);
        if (! $row) {
            return false;
        }
        return $this->convertRow($row, $with);
    }

    public function convertRow(Row $row, array $with)
    {
        $record = $this->recordFactory->newRecord($row, $this->relations->getFields());
        $this->relations->stitchIntoRecord($record, $with);
        return $record;
    }

    public function fetchRecordSet(array $primaryVals, array $with = array())
    {
        $rowSet = $this->table->fetchRowSet($primaryVals);
        if (! $rowSet) {
            return array();
        }
        return $this->convertRowSet($rowSet, $with);
    }

    public function fetchRecordSetBy(array $colsVals = [], array $with = array())
    {
        $rowSet = $this->table->fetchRowSetBy($colsVals);
        if (! $rowSet) {
            return array();
        }
        return $this->convertRowSet($rowSet, $with);
    }

    public function convertRowSet(RowSet $rowSet, array $with)
    {
        $recordSet = $this->newRecordSetFromRows($rowSet);
        $this->relations->stitchIntoRecordSet($recordSet, $with);
        return $recordSet;
    }

    protected function newMapperSelect(TableSelect $tableSelect)
    {
        return new MapperSelect($this, $tableSelect);
    }

    public function select(array $colsVals = [])
    {
        $tableSelect = $this->getTable()->select($colsVals);
        return $this->newMapperSelect($tableSelect);
    }

    public function insert(Record $record)
    {
        $this->recordFactory->assertRecordClass($record);
        $this->mapperEvents->beforeInsert($this, $record);
        return $this->getTable()->insert($record->getRow());
    }

    public function update(Record $record)
    {
        $this->recordFactory->assertRecordClass($record);
        $this->mapperEvents->beforeUpdate($this, $record);
        return $this->getTable()->update($record->getRow());
    }

    public function delete(Record $record)
    {
        $this->recordFactory->assertRecordClass($record);
        $this->mapperEvents->beforeDelete($this, $record);
        return $this->getTable()->delete($record->getRow());
    }
}
