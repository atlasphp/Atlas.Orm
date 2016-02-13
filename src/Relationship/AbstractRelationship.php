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

    protected $on = array();

    protected $throughName;

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

    public function on(array $on)
    {
        $this->on = $on;
        return $this;
    }

    protected function fix()
    {
        if ($this->fixed) {
            return;
        }

        $this->nativeMapper = $this->mapperLocator->get($this->nativeMapperClass);
        $this->foreignMapper = $this->mapperLocator->get($this->foreignMapperClass);
        $this->fixOn();

        $this->fixed = true;
    }

    protected function fixOn()
    {
        if ($this->on) {
            return;
        }

        foreach ($this->nativeMapper->getTable()->getPrimaryKey() as $col) {
            $this->on[$col] = $col;
        }
    }

    protected function whereCondVals(RecordInterface $nativeRecord)
    {
        if (count($this->on) == 1) {
            $nativeCol = key($this->on);
            $foreignCol = current($this->on);
            return [
                "$foreignCol = ?",
                [$nativeRecord->{$nativeCol}],
            ];
        }

        $cond = [];
        $vals = [];
        foreach ($this->on as $nativeCol => $foreignCol) {
            $cond[] = "$foreignCol = ?";
            $vals[] = $nativeRecord->$nativeCol;
        }
        $cond = '(' . implode(' AND ', $cond) . ')';
        return [$cond, $vals];
    }

    protected function selectForRecord(RecordInterface $nativeRecord, $custom)
    {
        $select = $this->foreignMapper->select();
        list($cond, $vals) = $this->whereCondVals($nativeRecord);
        $select->where($cond, ...$vals);
        if ($custom) {
            $custom($select);
        }
        return $select;
    }

    protected function selectForRecordSet(RecordSetInterface $nativeRecordSet, $custom)
    {
        $select = $this->foreignMapper->select();
        foreach ($nativeRecordSet as $nativeRecord) {
            list($cond, $vals) = $this->whereCondVals($nativeRecord);
            $select->orWhere($cond, ...$vals);
        }
        if ($custom) {
            $custom($select);
        }
        return $select;
    }

    protected function recordsMatch(
        RecordInterface $nativeRecord,
        RecordInterface $foreignRecord
    ) {
        foreach ($this->on as $nativeCol => $foreignCol) {
            if ($nativeRecord->$nativeCol != $foreignRecord->$foreignCol) {
                return false;
            }
        }
        return true;
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
