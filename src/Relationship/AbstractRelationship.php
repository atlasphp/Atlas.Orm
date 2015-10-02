<?php
namespace Atlas\Relationship;

use Atlas\Mapper\Mapper;
use Atlas\Mapper\MapperLocator;
use Atlas\Mapper\Record;
use Atlas\Mapper\RecordSet;
use Atlas\Mapper\Related;

abstract class AbstractRelationship
{
    protected $mapperLocator;

    protected $name;

    protected $nativeMapperClass;
    protected $nativeMapper;

    protected $foreignMapperClass;
    protected $forignMapper;

    protected $nativeCol;
    protected $throughNativeCol;
    protected $throughForeignCol;
    protected $foreignCol;
    protected $foreignClass;

    protected $orNone = false;

    protected $emptyValue = null;

    protected $fixed = false;

    public function __construct(
        MapperLocator $mapperLocator,
        $nativeMapperClass,
        $name,
        $foreignMapperClass,
        $throughName = null
    ) {
        $this->mapperLocator = $mapperLocator;
        $this->nativeMapperClass = $nativeMapperClass;
        $this->name = $name;
        $this->foreignMapperClass = $foreignMapperClass;
        $this->throughName = $throughName;
    }

    public function nativeCol($nativeCol)
    {
        $this->nativeCol = $nativeCol;
        return $this;
    }

    public function foreignCol($foreignCol)
    {
        $this->foreignCol = $foreignCol;
        return $this;
    }

    public function orNone($flag = true)
    {
        $this->orNone = (bool) $flag;
    }

    protected function fix()
    {
        if ($this->fixed) {
            return;
        }

        $this->nativeMapper = $this->mapperLocator->get($this->nativeMapperClass);
        $this->foreignMapper = $this->mapperLocator->get($this->foreignMapperClass);

        $this->fixNativeCol();
        $this->fixThroughNativeCol();
        $this->fixThroughForeignCol();
        $this->fixForeignCol();
        $this->fixForeignClass();
        $this->fixEmptyValue();

        $this->fixed = true;
    }

    protected function fixNativeCol()
    {
        if ($this->nativeCol) {
            return;
        }

        $this->nativeCol = $this->nativeMapper->getTable()->getPrimary();
    }

    protected function fixForeignCol()
    {
        if ($this->foreignCol) {
            return;
        }

        $this->foreignCol = $this->nativeMapper->getTable()->getPrimary();
    }

    protected function fixThroughNativeCol()
    {
    }

    protected function fixThroughForeignCol()
    {
    }

    protected function foreignSelect($foreignVal, callable $custom = null)
    {
        $select = $this->foreignMapper->select([$this->foreignCol => $foreignVal]);
        if ($custom) {
            $custom($select);
        }
        return $select;
    }

    protected function getUniqueVals($set, $col)
    {
        $vals = [];
        foreach ($set as $item) {
            $vals[] = $item->$col;
        }
        return array_unique($vals);
    }

    protected function groupRecordSets($recordSet, $field)
    {
        $groups = array();
        foreach ($recordSet as $record) {
            $key = $record->$field;
            if (! isset($groups[$key])) {
                $groups[$key] = $this->foreignMapper->newRecordSet([]);
            }
            $groups[$key][] = $record;
        }
        return $groups;
    }

    abstract protected function fixEmptyValue();

    abstract protected function fixForeignClass();

    abstract protected function getMissing();

    abstract public function stitchIntoRecord(
        Record $nativeRecord,
        callable $custom = null
    );

    abstract public function stitchIntoRecordSet(
        RecordSet $nativeRecord,
        callable $custom = null
    );
}
