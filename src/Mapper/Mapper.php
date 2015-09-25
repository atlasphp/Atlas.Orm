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

    protected $relations;

    protected $recordClass;

    protected $recordSetClass;

    public function __construct(Table $table)
    {
        $this->table = $table;
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

    // row can be array or Row object
    public function newRecord($row)
    {
        if (is_array($row)) {
            $row = $this->getTable()->newRow($row);
        }

        $recordClass = $this->getRecordClass();
        return new $recordClass(
            $row,
            $this->relations->getEmptyFields()
        );
    }

    // rowSet can be array or RowSet object
    public function newRecordSet($rowSet)
    {
        $records = [];
        foreach ($rowSet as $row) {
            $records[] = $this->newRecord($row);
        }

        $recordSetClass = $this->getRecordSetClass();
        return new $recordSetClass($records);
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

    public function fetchRecordBySelect(TableSelect $tableSelect)
    {
        $record = false;
        $row = $this->table->fetchRowBySelect($tableSelect);
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

    public function fetchRecordsBySelect(TableSelect $tableSelect, $col)
    {
        $rows = $this->table->fetchRowsBySelect($tableSelect, $col);
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

    public function fetchRecordSetBySelect(TableSelect $tableSelect)
    {
        $rowSet = $this->table->fetchRowSetBySelect($tableSelect);
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

    public function fetchRecordSetsBySelect(TableSelect $tableSelect, $col)
    {
        $rowSets = $this->table->fetchRowSetsBySelect($tableSelect, $col);
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

    public function getRecordClass()
    {
        if (! $this->recordClass) {
            // Foo\Bar\BazMapper -> Foo\Bar\BazRecord
            $class = substr(get_class($this), -6);
            $this->recordClass = "{$class}Record";
        }

        if (! class_exists($this->recordClass)) {
            $this->recordClass = 'Atlas\Mapper\Record';
        }

        return $this->recordClass;
    }

    public function getRecordSetClass()
    {
        if (! $this->recordSetClass) {
            // Foo\Bar\BazMapper -> Foo\Bar\BazRecordSet
            $class = substr(get_class($this), -6);
            $this->recordSetClass = "{$class}RecordSet";
        }

        if (! class_exists($this->recordSetClass)) {
            $this->recordSetClass = 'Atlas\Mapper\RecordSet';
        }

        return $this->recordSetClass;
    }
}
