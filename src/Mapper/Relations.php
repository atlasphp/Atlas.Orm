<?php
namespace Atlas\Mapper;

use Atlas\Atlas;
use Atlas\Mapper\Mapper;
use Atlas\Relationship\ManyToMany;
use Atlas\Relationship\ManyToOne;
use Atlas\Relationship\OneToMany;
use Atlas\Relationship\OneToOne;

class Relations
{
    protected $relations = [];

    protected $emptyFields = [];

    protected $nativeMapperClass;

    public function __construct($nativeMapperClass)
    {
        $this->nativeMapperClass = $nativeMapperClass;
    }

    public function getEmptyFields()
    {
        return $this->emptyFields;
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

    public function manyToMany($name, $foreignMapperClass, $joinField)
    {
        return $this->set($name, ManyToMany::CLASS, $foreignMapperClass, $joinField);
    }

    public function set($name, $relationClass, $foreignMapperClass, $joinField = null)
    {
        $relation = new $relationClass(
            $this->nativeMapperClass,
            $name,
            $foreignMapperClass,
            $joinField
        );

        $this->relations[$name] = $relation;
        $this->emptyFields[$name] = null;
        return $relation;
    }

    public function stitchIntoRecord(Atlas $atlas, Record $record, array $with)
    {
        foreach ($this->fixWith($with) as $name => $custom) {
            $this->relations[$name]->stitchIntoRecord(
                $atlas,
                $record,
                $custom
            );
        }
    }

    public function stitchIntoRecordSet(Atlas $atlas, RecordSet $recordSet, array $with)
    {
        foreach ($this->fixWith($with) as $name => $custom) {
            $this->relations[$name]->stitchIntoRecordSet(
                $atlas,
                $recordSet,
                $custom
            );
        }
        return $recordSet;
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
