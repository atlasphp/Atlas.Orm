<?php
namespace Atlas\Orm\Relationship;

use Atlas\Orm\Mapper\MapperLocator;
use Atlas\Orm\Mapper\RecordInterface;
use Atlas\Orm\Mapper\RecordSetInterface;

abstract class AbstractRelationship
{
    protected $mapperLocator;

    protected $name;

    protected $nativeMapperClass;
    protected $nativeMapper;

    protected $foreignMapperClass;
    protected $foreignMapper;

    protected $nativeKey;
    protected $throughName;
    protected $throughNativeKey;
    protected $throughForeignKey;
    protected $foreignKey;

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

    public function nativeKey($nativeKey)
    {
        $this->nativeKey = $nativeKey;
        return $this;
    }

    public function foreignKey($foreignKey)
    {
        $this->foreignKey = $foreignKey;
        return $this;
    }

    protected function fix()
    {
        if ($this->fixed) {
            return;
        }

        $this->nativeMapper = $this->mapperLocator->get($this->nativeMapperClass);
        $this->foreignMapper = $this->mapperLocator->get($this->foreignMapperClass);

        $this->fixNativeKey();
        $this->fixThroughNativeKey();
        $this->fixThroughForeignKey();
        $this->fixForeignKey();

        $this->fixed = true;
    }

    protected function fixNativeKey()
    {
        if ($this->nativeKey) {
            return;
        }

        $primaryKey = $this->nativeMapper->getTable()->getPrimaryKey();
        $primaryCol = $primaryKey[0];
        $this->nativeKey($primaryCol);
    }

    protected function fixForeignKey()
    {
        if ($this->foreignKey) {
            return;
        }

        $primaryKey = $this->nativeMapper->getTable()->getPrimaryKey();
        $primaryCol = $primaryKey[0];
        $this->foreignKey($primaryCol);
    }

    protected function fixThroughNativeKey()
    {
    }

    protected function fixThroughForeignKey()
    {
    }

    protected function foreignSelect($foreignVal, callable $custom = null)
    {
        $select = $this->foreignMapper->select([$this->foreignKey => $foreignVal]);
        if ($custom) {
            $custom($select);
        }
        return $select;
    }

    protected function getUniqueVals(RecordSetInterface $recordSet, $col)
    {
        $vals = [];
        foreach ($recordSet as $record) {
            $vals[] = $record->$col;
        }
        return array_unique($vals);
    }

    protected function groupRecordSets($recordSet, $field)
    {
        $groups = [];
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
        RecordInterface $nativeRecord,
        callable $custom = null
    );

    abstract public function stitchIntoRecordSet(
        RecordSetInterface $nativeRecordSet,
        callable $custom = null
    );
}
