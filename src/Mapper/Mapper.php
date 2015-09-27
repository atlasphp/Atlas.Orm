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
            throw new Exception("{$this->recordClass} not defined.");
        }

        $this->recordSetClass = "{$type}RecordSet";
        if (! class_exists($this->recordSetClass)) {
            throw new Exception("{$this->recordSetClass} not defined.");
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
