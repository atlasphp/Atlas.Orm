<?php
namespace Atlas\Mapper;

use Atlas\Exception;

class MapperRelations
{
    protected $relations = [];

    protected $nativeMapperClass;

    protected $mapperLocator;

    protected $fields = [];

    public function __construct(
        $nativeMapperClass,
        MapperLocator $mapperLocator
    ) {
        $this->nativeMapperClass = $nativeMapperClass;
        $this->mapperLocator = $mapperLocator;
    }

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
            $this->nativeMapperClass,
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
