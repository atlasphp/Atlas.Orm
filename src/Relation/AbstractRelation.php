<?php
namespace Atlas\Orm\Relation;

use Atlas\Orm\Mapper\Mapper;
use Atlas\Orm\Mapper\MapperLocator;
use Atlas\Orm\Mapper\Record;
use Atlas\Orm\Mapper\RecordSet;
use Atlas\Orm\Mapper\Related;

abstract class AbstractRelation
{
    protected $mapperLocator;

    protected $name;

    protected $nativeMapperClass;
    protected $nativeMapper;

    protected $foreignMapperClass;
    protected $foreignMapper;

    protected $nativeCol;
    protected $throughName;
    protected $throughNativeCol;
    protected $throughForeignCol;
    protected $foreignCol;

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

    public function getSettings()
    {
        $this->fix();
        $settings = get_object_vars($this);
        unset($settings['fixed']);
        unset($settings['mapperLocator']);
        unset($settings['nativeMapper']);
        unset($settings['foreignMapper']);
        unset($settings['mapperLocator']);
        return $settings;
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

        $this->fixed = true;
    }

    protected function fixNativeCol()
    {
        if ($this->nativeCol) {
            return;
        }

        $this->nativeCol($this->nativeMapper->getGateway()->tablePrimary());
    }

    protected function fixForeignCol()
    {
        if ($this->foreignCol) {
            return;
        }

        $this->foreignCol($this->nativeMapper->getGateway()->tablePrimary());
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

    protected function getUniqueVals(RecordSet $recordSet, $col)
    {
        $vals = [];
        foreach ($recordSet as $record) {
            $vals[] = $record->$col;
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

    abstract public function stitchIntoRecord(
        Record $nativeRecord,
        callable $custom = null
    );

    abstract public function stitchIntoRecordSet(
        RecordSet $nativeRecordSet,
        callable $custom = null
    );
}
