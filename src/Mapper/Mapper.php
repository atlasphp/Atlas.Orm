<?php
namespace Atlas\Mapper;

use Atlas\Exception;
use Atlas\Table\Row;
use Atlas\Table\RowSet;
use Atlas\Table\Table;
use Atlas\Table\TableSelect;

/**
 *
 * A DataMapper that returns Record and RecordSet objects and defines
 * relationships for those Records.
 *
 * @todo An assertion to check that Record and RecordSet are of the right type.
 *
 * @package Atlas.Atlas
 *
 */
class Mapper
{
    protected $table;

    protected $relations;

    protected $recordClass;

    protected $recordSetClass;

    public function __construct(Table $table, Relations $relations)
    {
        $this->table = $table;
        $this->relations = $relations;

        // Foo\Bar\BazMapper -> Foo\Bar\Baz
        $type = substr(get_class($this), 0, -6);

        $this->recordClass = "{$type}Record";
        if (! class_exists($this->recordClass)) {
            throw new Exception("{$this->recordClass} does not exist");
        }

        $this->recordSetClass = "{$type}RecordSet";
        if (! class_exists($this->recordSetClass)) {
            throw new Exception("{$this->recordSetClass} does not exist");
        }

        $this->setRelations();
    }

    public function getTable()
    {
        return $this->table;
    }

    public function getRelations()
    {
        return $this->relations;
    }

    protected function setRelations()
    {
    }

    public function getRecordClass()
    {
        return $this->recordClass;
    }

    public function getRecordSetClass()
    {
        return $this->recordSetClass;
    }

    // row can be array or Row object
    public function newRecord($row, array $related = [])
    {
        if (is_array($row)) {
            $row = $this->getTable()->newRow($row);
        }

        $recordClass = $this->getRecordClass();
        return new $recordClass($row, $related);
    }

    // rowSet can be array of Rows, or RowSet object
    public function newRecordSet($rowSet, array $relatedSet = [])
    {
        $records = [];
        foreach ($rowSet as $row) {
            $primaryVal = $row->getPrimaryVal();
            $related = isset($relatedSet[$primaryVal])
                     ? $relatedSet[$primaryVal]
                     : array();
            $records[] = $this->newRecord($row, $related);
        }

        $recordSetClass = $this->getRecordSetClass();
        return new $recordSetClass($records);
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

        $related = $this->relations->fetchForRow($row, $with);
        return $this->newRecord($row, $related);
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

        $relatedSet = $this->relations->fetchForRowSet($rowSet, $with);
        return $this->newRecordSet($rowSet, $relatedSet);
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
