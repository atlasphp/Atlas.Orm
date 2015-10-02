<?php
namespace Atlas\Mapper;

use Atlas\Exception;
use Atlas\Relationship\HasManyThrough;
use Atlas\Relationship\BelongsTo;
use Atlas\Relationship\HasMany;
use Atlas\Relationship\HasOne;
use Atlas\Table\Row;
use Atlas\Table\RowSet;

class Relations
{
    protected $relations = [];

    protected $nativeMapperClass;

    protected $mapperLocator;

    public function __construct(
        $nativeMapperClass,
        MapperLocator $mapperLocator
    ) {
        $this->nativeMapperClass = $nativeMapperClass;
        $this->mapperLocator = $mapperLocator;
    }

    public function getDefinitions()
    {
        $defs = [];
        foreach ($this->relations as $name => $relation) {
            $defs[$name] = null;
        }
        return $defs;
    }

    public function hasOne($name, $foreignMapperClass)
    {
        return $this->set($name, HasOne::CLASS, $foreignMapperClass);
    }

    public function hasMany($name, $foreignMapperClass)
    {
        return $this->set($name, HasMany::CLASS, $foreignMapperClass);
    }

    public function belongsTo($name, $foreignMapperClass)
    {
        $this->set($name, BelongsTo::CLASS, $foreignMapperClass);
    }

    public function hasManyThrough($name, $foreignMapperClass, $throughName)
    {
        if (! isset($this->relations[$throughName])) {
            throw new Exception("Relation '$throughName' does not exist");
        }

        return $this->set(
            $name,
            HasManyThrough::CLASS,
            $foreignMapperClass,
            $throughName
        );
    }

    public function set($name, $relationClass, $foreignMapperClass, $throughName = null)
    {
        if (! class_exists($foreignMapperClass)) {
            throw new Exception("$foreignMapperClass does not exist");
        }

        $relation = $this->newRelation($name, $relationClass, $foreignMapperClass, $throughName);
        $this->relations[$name] = $relation;
        return $relation;
    }

    protected function newRelation($name, $relationClass, $foreignMapperClass, $throughName = null)
    {
        return new $relationClass(
            $this->mapperLocator,
            $this->nativeMapperClass,
            $name,
            $foreignMapperClass,
            $throughName
        );
    }

    public function stitchIntoRecord(Record $record, array $with = [])
    {
        foreach ($this->fixWith($with) as $name => $custom) {
            $this->relations[$name]->stitchIntoRecord(
                $record,
                $custom
            );
        }
    }

    public function stitchIntoRecordSet(RecordSet $recordSet, array $with = [])
    {
        foreach ($this->fixWith($with) as $name => $custom) {
            $this->relations[$name]->stitchIntoRecordSet(
                $recordSet,
                $custom
            );
        }
    }

    protected function fixWith($spec)
    {
        $with = [];
        foreach ($spec as $key => $val) {
            if (is_int($key)) {
                $with[$val] = null;
            } else {
                $with[$key] = $val;
            }
        }
        return $with;
    }
}
