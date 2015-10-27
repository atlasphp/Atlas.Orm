<?php
namespace Atlas\Mapper;

use Atlas\Exception;
use Atlas\Table\AbstractRow;
use Atlas\Table\AbstractRowSet;
use Atlas\Table\AbstractTable;
use Atlas\Table\TableSelect;

/**
 *
 * A data source mapper that returns Record and RecordSet objects.
 *
 * @package Atlas.Atlas
 *
 */
abstract class AbstractMapper
{
    protected $table;

    protected $relations;

    protected $recordFactory;

    protected $recordFilter;

    public function __construct(
        AbstractTable $table,
        AbstractRecordFactory $recordFactory,
        AbstractRecordFilter $recordFilter,
        AbstractRelations $relations
    ) {
        $this->table = $table;
        $this->recordFactory = $recordFactory;
        $this->recordFilter = $recordFilter;
        $this->relations = $relations;
    }

    public function getTable()
    {
        return $this->table;
    }

    public function getRelations()
    {
        return $this->relations;
    }

    // row can be array or Row object
    public function newRecord($row = [])
    {
        if (is_array($row)) {
            $row = $this->getTable()->newRow($row);
        }

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
        return $this->convertRow($row, $with);
    }

    public function fetchRecordBy(array $colsVals = [], array $with = [])
    {
        $row = $this->table->fetchRowBy($colsVals);
        return $this->convertRow($row, $with);
    }

    public function convertRow($row, array $with)
    {
        if (! $row) {
            return false;
        }

        $record = $this->recordFactory->newRecord($row, $this->relations->getFields());
        $this->relations->stitchIntoRecord($record, $with);
        return $record;
    }

    public function fetchRecordSet(array $primaryVals, array $with = array())
    {
        $rowSet = $this->table->fetchRowSet($primaryVals);
        return $this->convertRowSet($rowSet, $with);
    }

    public function fetchRecordSetBy(array $colsVals = [], array $with = array())
    {
        $rowSet = $this->table->fetchRowSetBy($colsVals);
        return $this->convertRowSet($rowSet, $with);
    }

    public function convertRowSet($rowSet, array $with)
    {
        if (! $rowSet) {
            return array();
        }

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

    public function insert(AbstractRecord $record)
    {
        $this->recordFactory->assertRecordClass($record);
        $this->recordFilter->forInsert($record);
        return $this->getTable()->insert($record->getRow());
    }

    public function update(AbstractRecord $record)
    {
        $this->recordFactory->assertRecordClass($record);
        $this->recordFilter->forUpdate($record);
        return $this->getTable()->update($record->getRow());
    }

    public function delete(AbstractRecord $record)
    {
        $this->recordFactory->assertRecordClass($record);
        return $this->getTable()->delete($record->getRow());
    }
}
