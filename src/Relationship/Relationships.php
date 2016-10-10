<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Orm\Relationship;

use Atlas\Orm\Exception;
use Atlas\Orm\Mapper\MapperLocator;
use Atlas\Orm\Mapper\RecordInterface;

/**
 *
 * __________
 *
 * @package atlas/orm
 *
 */
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

    public function oneToOne(
        $name,
        $nativeMapperClass,
        $foreignMapperClass
    ) {
        return $this->set(
            $name,
            OneToOne::CLASS,
            $nativeMapperClass,
            $foreignMapperClass
        );
    }

    public function oneToMany(
        $name,
        $nativeMapperClass,
        $foreignMapperClass
    ) {
        return $this->set(
            $name,
            OneToMany::CLASS,
            $nativeMapperClass,
            $foreignMapperClass
        );
    }

    public function manyToOne(
        $name,
        $nativeMapperClass,
        $foreignMapperClass
    ) {
        return $this->set(
            $name,
            ManyToOne::CLASS,
            $nativeMapperClass,
            $foreignMapperClass
        );
    }

    public function manyToMany(
        $name,
        $nativeMapperClass,
        $foreignMapperClass,
        $throughName
    ) {
        return $this->set(
            $name,
            ManyToMany::CLASS,
            $nativeMapperClass,
            $foreignMapperClass,
            $throughName
        );
    }

    protected function set(
        $name,
        $relationClass,
        $nativeMapperClass,
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
            $relationClass,
            $name,
            $nativeMapperClass,
            $foreignMapperClass,
            $throughName
        );

        return $this->defs[$name];
    }

    protected function newRelation(
        $relationClass,
        $name,
        $nativeMapperClass,
        $foreignMapperClass,
        $throughName = null
    ) {
        return new $relationClass(
            $name,
            $this->mapperLocator,
            $nativeMapperClass,
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
        array $records,
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
