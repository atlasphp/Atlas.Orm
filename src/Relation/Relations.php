<?php
namespace Atlas\Orm\Relation;

use Atlas\Orm\Exception;
use Atlas\Orm\Mapper\MapperLocator;
use Atlas\Orm\Mapper\Record;
use Atlas\Orm\Mapper\RecordSet;

class Relations
{
    protected $mapperLocator;

    protected $relations = [];

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

        if ($throughName && ! isset($this->relations[$throughName])) {
            throw Exception::relationDoesNotExist($throughName);
        }

        $this->fields[$name] = null;
        $this->relations[$name] = $this->newRelation(
            $nativeMapperClass,
            $name,
            $relationClass,
            $foreignMapperClass,
            $throughName
        );

        return $this->relations[$name];
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
