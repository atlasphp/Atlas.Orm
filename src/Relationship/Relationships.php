<?php
namespace Atlas\Orm\Relationship;

use Atlas\Orm\Exception;
use Atlas\Orm\Mapper\MapperLocator;
use Atlas\Orm\Mapper\RecordInterface;
use Atlas\Orm\Mapper\RecordSetInterface;

class Relationships
{
    protected $mapperLocator;

    protected $defs = [];

    protected $fields = [];

    public function __construct(MapperLocator $mapperLocator)
    {
        $this->mapperLocator = $mapperLocator;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function set(
        $nativeMapperClass,
        $name,
        $relationClass,
        $foreignMapperClass,
        $throughName = null
    ) {
        if (! class_exists($foreignMapperClass)) {
            throw Exception::classDoesNotExist($foreignMapperClass);
        }

        if ($throughName && ! isset($this->defs[$throughName])) {
            throw Exception::relationDoesNotExist($throughName);
        }

        $this->fields[$name] = null;
        $this->defs[$name] = $this->newRelation(
            $nativeMapperClass,
            $name,
            $relationClass,
            $foreignMapperClass,
            $throughName
        );

        return $this->defs[$name];
    }

    protected function newRelation(
        $nativeMapperClass,
        $name,
        $relationClass,
        $foreignMapperClass,
        $throughName = null
    ) {
        return new $relationClass(
            $this->mapperLocator,
            $nativeMapperClass,
            $name,
            $foreignMapperClass,
            $throughName
        );
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

    public function stitchIntoRecords(
        /* traversable */ $records,
        array $with = []
    ) {
        foreach ($this->fixWith($with) as $name => $custom) {
            $this->defs[$name]->stitchIntoRecords(
                $records,
                $custom
            );
        }
    }
}
