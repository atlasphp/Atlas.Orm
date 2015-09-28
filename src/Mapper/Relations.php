<?php
namespace Atlas\Mapper;

use Atlas\Exception;
use Atlas\Relationship\ManyToMany;
use Atlas\Relationship\ManyToOne;
use Atlas\Relationship\OneToMany;
use Atlas\Relationship\OneToOne;
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

    public function oneToOne($name, $foreignMapperClass)
    {
        return $this->set($name, OneToOne::CLASS, $foreignMapperClass);
    }

    public function oneToMany($name, $foreignMapperClass)
    {
        return $this->set($name, OneToMany::CLASS, $foreignMapperClass);
    }

    public function manyToOne($name, $foreignMapperClass)
    {
        $this->set($name, ManyToOne::CLASS, $foreignMapperClass);
    }

    public function manyToMany($name, $foreignMapperClass, $throughName)
    {
        if (! isset($this->relations[$throughName])) {
            throw new Exception("Relation '$throughName' does not exist");
        }

        return $this->set(
            $name,
            ManyToMany::CLASS,
            $foreignMapperClass,
            $throughName
        );
    }

    public function set($name, $relationClass, $foreignMapperClass, $throughName = null)
    {
        if (! class_exists($foreignMapperClass)) {
            throw new Exception("$foreignMapperClass does not exist");
        }

        $relation = new $relationClass(
            $this->nativeMapperClass,
            $name,
            $foreignMapperClass,
            $throughName
        );

        $this->relations[$name] = $relation;
        return $relation;
    }

    public function fetchForRow(Row $row, array $with = [])
    {
        $related = [];
        foreach ($this->fixWith($with) as $name => $custom) {
            $this->relations[$name]->fetchForRow(
                $this->mapperLocator,
                $row,
                $related, // should this be an object?
                $custom
            );
        }
        return $related;
    }

    public function fetchForRowSet(RowSet $rowSet, array $with = [])
    {
        $relatedSet = [];
        foreach ($this->fixWith($with) as $name => $custom) {
            $this->relations[$name]->fetchForRowSet(
                $this->mapperLocator,
                $rowSet,
                $relatedSet, // should this be an object?
                $custom
            );
        }
        return $relatedSet;
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
