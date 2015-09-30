<?php
namespace Atlas\Mapper;

use Atlas\Exception;
use Atlas\Table\Row;
use Atlas\Table\RowSet;
use Atlas\Table\Table;
use Atlas\Table\TableSelect;
use InvalidArgumentException;

/**
 *
 * A DataMapper that returns Record and RecordSet objects.
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
    public function newRecord($row, Related $related = null)
    {
        if (is_array($row)) {
            $row = $this->getTable()->newRow($row);
        }

        if (! $related) {
            $related = new Related($this->relations->getDefinitions());
        }

        $recordClass = $this->getRecordClass();
        return new $recordClass($row, $related);
    }

    // rowSet can be array of Rows, or RowSet object
    public function newRecordSetFromRows($rows, RelatedSet $relatedSet)
    {
        $records = [];
        foreach ($rows as $row) {
            $primaryVal = $row->getPrimaryVal();
            $related = $relatedSet->get($primaryVal);
            $records[] = $this->newRecord($row, $related);
        }
        return $this->newRecordSet($records);
    }

    public function newRecordSet(array $records = [])
    {
        $recordSetClass = $this->getRecordSetClass();
        return new $recordSetClass($records, $this->recordClass);
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
        return $this->newRecordSetFromRows($rowSet, $relatedSet);
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
        $this->assertRecordClass($record);
        return $this->getTable()->insert($record->getRow());
    }

    public function update(Record $record)
    {
        $this->assertRecordClass($record);
        return $this->getTable()->update($record->getRow());
    }

    public function delete(Record $record)
    {
        $this->assertRecordClass($record);
        return $this->getTable()->delete($record->getRow());
    }

    protected function assertRecordClass(Record $record)
    {
        if (! $record instanceof $this->recordClass) {
            $actual = get_class($record);
            throw new InvalidArgumentException("Expected {$this->recordClass}, got {$actual} instead");
        }
    }
}
