<?php
namespace Atlas\Mapper;

use Atlas\Exception;
use Atlas\Relation\BelongsTo;
use Atlas\Relation\HasMany;
use Atlas\Relation\HasManyThrough;
use Atlas\Relation\HasOne;
use Atlas\Table\AbstractRow;
use Atlas\Table\AbstractRowSet;
use Atlas\Table\AbstractTable;
use Atlas\Table\TableSelect;
use InvalidArgumentException;

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

    protected $mapperRelations;

    protected $recordClass;

    protected $recordSetClass;

    public function __construct(AbstractTable $table, MapperRelations $mapperRelations)
    {
        $this->table = $table;
        $this->mapperRelations = $mapperRelations;

        // Foo\Bar\BazMapper -> Foo\Bar\Baz
        $type = substr(get_class($this), 0, -6);
        $this->recordClass = "{$type}Record";
        $this->recordSetClass = "{$type}RecordSet";

        $this->setMapperRelations();
    }

    public function getTable()
    {
        return $this->table;
    }

    public function getMapperRelations()
    {
        return $this->mapperRelations;
    }

    protected function setMapperRelations()
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
    public function newRecord($row = [])
    {
        if (is_array($row)) {
            $row = $this->getTable()->newRow($row);
        }

        $related = new Related($this->mapperRelations->getFields());
        $recordClass = $this->getRecordClass();
        return new $recordClass($row, $related);
    }

    // rowSet can be array of Rows, or RowSet object
    public function newRecordSetFromRows($rows)
    {
        $records = [];
        foreach ($rows as $row) {
            $records[] = $this->newRecord($row);
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

        $record = $this->newRecord($row);
        $this->mapperRelations->stitchIntoRecord($record, $with);
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
        $this->mapperRelations->stitchIntoRecordSet($recordSet, $with);
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

    protected function hasOne($name, $foreignMapperClass)
    {
        return $this->mapperRelations->set(
            $name,
            HasOne::CLASS,
            $foreignMapperClass
        );
    }

    protected function hasMany($name, $foreignMapperClass)
    {
        return $this->mapperRelations->set(
            $name,
            HasMany::CLASS,
            $foreignMapperClass
        );
    }

    protected function belongsTo($name, $foreignMapperClass)
    {
        $this->mapperRelations->set(
            $name,
            BelongsTo::CLASS,
            $foreignMapperClass
        );
    }

    protected function hasManyThrough($name, $foreignMapperClass, $throughName)
    {
        return $this->mapperRelations->set(
            $name,
            HasManyThrough::CLASS,
            $foreignMapperClass,
            $throughName
        );
    }
}
