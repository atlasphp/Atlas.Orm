<?php
namespace Atlas\Mapper;

use Atlas\Exception;
use Atlas\Relation\BelongsTo;
use Atlas\Relation\HasMany;
use Atlas\Relation\HasManyThrough;
use Atlas\Relation\HasOne;

abstract class AbstractRelations
{
    protected $relations = [];

    protected $mapperLocator;

    protected $fields = [];

    public function __construct(MapperLocator $mapperLocator)
    {
        $this->mapperLocator = $mapperLocator;
        $this->setRelations();
    }

    abstract protected function getNativeMapperClass();

    abstract protected function setRelations();

    public function getFields()
    {
        return $this->fields;
    }

    public function set($name, $relationClass, $foreignMapperClass, $throughName = null)
    {
        if (! class_exists($foreignMapperClass)) {
            throw new Exception("$foreignMapperClass does not exist");
        }

        if ($throughName && ! isset($this->relations[$throughName])) {
            throw new Exception("Relation '$throughName' does not exist");
        }

        $relation = $this->newRelation($name, $relationClass, $foreignMapperClass, $throughName);
        $this->fields[$name] = null;
        $this->relations[$name] = $relation;
        return $relation;
    }

    protected function newRelation($name, $relationClass, $foreignMapperClass, $throughName = null)
    {
        return new $relationClass(
            $this->mapperLocator,
            $this->getNativeMapperClass(),
            $name,
            $foreignMapperClass,
            $throughName
        );
    }

    public function stitchIntoRecord(AbstractRecord $record, array $with = [])
    {
        foreach ($this->fixWith($with) as $name => $custom) {
            $this->relations[$name]->stitchIntoRecord(
                $record,
                $custom
            );
        }
    }

    public function stitchIntoRecordSet(AbstractRecordSet $recordSet, array $with = [])
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

    protected function hasOne($name, $foreignMapperClass)
    {
        return $this->set(
            $name,
            HasOne::CLASS,
            $foreignMapperClass
        );
    }

    protected function hasMany($name, $foreignMapperClass)
    {
        return $this->set(
            $name,
            HasMany::CLASS,
            $foreignMapperClass
        );
    }

    protected function belongsTo($name, $foreignMapperClass)
    {
        $this->set(
            $name,
            BelongsTo::CLASS,
            $foreignMapperClass
        );
    }

    protected function hasManyThrough($name, $foreignMapperClass, $throughName)
    {
        return $this->set(
            $name,
            HasManyThrough::CLASS,
            $foreignMapperClass,
            $throughName
        );
    }
}
