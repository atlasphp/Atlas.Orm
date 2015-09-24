<?php
namespace Atlas\Mapper;

use Atlas\Table\Row;
use Atlas\Table\RowSet;
use Atlas\Table\Table;
use Atlas\Table\TableSelect;

// do we even *do* selects at this level? they require knowledge of the other
// mappers, don't they? or is it enough to construct a "plain" record, and let
// Atlas fill in the related values?
//
class Mapper
{
    protected $table;

    protected $recordFactory;

    protected $relations;

    public function __construct(
        Table $table,
        RecordFactory $recordFactory
    ) {
        $this->table = $table;
        $this->recordFactory = $recordFactory;
        $this->relations = $this->newRelations();
        $this->addRelations();
    }

    protected function newRelations()
    {
        return new Relations($this);
    }

    public function getTable()
    {
        return $this->table;
    }

    public function getRelations()
    {
        return $this->relations;
    }

    protected function addRelations()
    {
    }

    public function newRecord($row)
    {
        if (is_array($row)) {
            $row = $this->getTable()->newRow($row);
        }

        return $this->recordFactory->newRecord(
            $row,
            $this->relations->getEmptyFields()
        );
    }

    public function newRecordSet(RowSet $rowSet)
    {
        $records = [];
        foreach ($rowSet as $row) {
            $records[] = $this->newRecord($row);
        }
        return $this->recordFactory->newRecordSet($records);
    }

    public function fetchRecord($primaryVal)
    {
        $record = false;
        $row = $this->table->fetchRow($primaryVal);
        if ($row) {
            $record = $this->newRecord($row);
        }
        return $record;
    }

    public function fetchRecordBy(array $colsVals = [], callable $custom = null)
    {
        $record = false;
        $row = $this->table->fetchRowBy($colsVals, $custom);
        if ($row) {
            $record = $this->newRecord($row);
        }
        return $record;
    }

    public function fetchRecordBySelect(TableSelect $select)
    {
        $record = false;
        $row = $this->table->fetchRowBySelect($select);
        if ($row) {
            $record = $this->newRecord($row);
        }
        return $record;
    }

    public function fetchRecords($primaryVals)
    {
        $rows = $this->table->fetchRows($primaryVals);
        return $this->groupRecords($rows);
    }

    public function fetchRecordsBy($colsVals, $col, callable $custom = null)
    {
        $rows = $this->table->fetchRowsBy($colsVals, $col, $custom);
        return $this->groupRecords($rows);
    }

    public function fetchRecordsBySelect(TableSelect $select, $col)
    {
        $rows = $this->table->fetchRowsBySelect($select, $col);
        return $this->groupRecords($rows);
    }

    protected function groupRecords(array $rows)
    {
        $records = [];
        foreach ($rows as $key => $row) {
            $records[$key] = $this->newRecord($row);
        }
        return $records;
    }

    public function fetchRecordSet(array $primaryVals)
    {
        $rowSet = $this->table->fetchRowSet($primaryVals);
        if (! $rowSet) {
            return array();
        }
        return $this->newRecordSet($rowSet);
    }

    public function fetchRecordSetBy(array $colsVals = [], callable $custom = null)
    {
        $rowSet = $this->table->fetchRowSetBy($colsVals, $custom);
        if (! $rowSet) {
            return array();
        }
        return $this->newRecordSet($rowSet);
    }

    public function fetchRecordSetBySelect(TableSelect $select)
    {
        $rowSet = $this->table->fetchRowSetBySelect($select);
        if (! $rowSet) {
            return array();
        }
        return $this->newRecordSet($rowSet);
    }

    public function fetchRecordSets($primaryVals, $col)
    {
        $rowSets = $this->table->fetchRowSets($primaryVals, $col);
        return $this->groupRecordSets($rowSets);
    }

    public function fetchRecordSetsBy($colsVals, $col, callable $custom = null)
    {
        $rowSets = $this->table->fetchRowSetsBy($colsVals, $col, $custom);
        return $this->groupRecordSets($rowSets);
    }

    public function fetchRecordSetsBySelect(TableSelect $select, $col)
    {
        $rowSets = $this->table->fetchRowSetsBySelect($select, $col);
        return $this->groupRecordSets($rowSets);
    }

    protected function groupRecordSets(array $rowSets)
    {
        $recordSets = [];
        foreach ($rowSets as $key => $rowSet) {
            $recordSets[$key] = $this->newRecordSet($rowSet);
        }
        return $recordSets;
    }

    public function select(array $colsVals = [])
    {
        return $this->getTable()->select($colsVals);
    }

    public function insert(Record $record)
    {
        return $this->getTable()->insert($record->getRow());
    }

    public function update(Record $record)
    {
        return $this->getTable()->update($record->getRow());
    }

    public function delete(Record $record)
    {
        return $this->getTable()->delete($record->getRow());
    }
}
