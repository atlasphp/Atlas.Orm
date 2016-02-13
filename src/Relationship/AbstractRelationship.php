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

    protected function selectForRecord(RecordInterface $record, $custom)
    {
        $select = $this->foreignMapper->select();
        list($cond, $vals) = $this->whereForRecord($select, $record);
        if ($custom) {
            $custom($select);
        }
        return $select;
    }

    protected function whereForRecord($select, RecordInterface $record)
    {
        if (count($this->on) > 1) {
            return $this->whereForRecordComposite($select, $record);
        }

        $nativeCol = key($this->on);
        $foreignCol = current($this->on);
        $select->where("{$foreignCol} = ?", $record->{$nativeCol});
    }

    protected function whereForRecordComposite($select, RecordInterface $record)
    {
        $cond = [];
        $vals = [];
        foreach ($this->on as $nativeCol => $foreignCol) {
            $cond[] = "{$foreignCol} = ?";
            $vals[] = $record->$nativeCol;
        }
        $cond = '(' . implode(' AND ', $cond) . ')';
        $select->where($cond, ...$vals);
    }

    protected function selectForRecordSet(RecordSetInterface $recordSet, $custom)
    {
        $select = $this->foreignMapper->select();
        $this->whereForRecordSet($select, $recordSet);
        if ($custom) {
            $custom($select);
        }
        return $select;
    }

    protected function whereForRecordSet($select, RecordSetInterface $recordSet)
    {
        if (count($this->on) > 1) {
            return $this->whereForRecordSetComposite($select, $recordSet);
        }

        $vals = [];
        $nativeCol = key($this->on);
        foreach ($recordSet as $record) {
            $vals[] = $record->{$nativeCol};
        }

        $foreignCol = current($this->on);
        $select->where("{$foreignCol} IN (?)", array_unique($vals));
    }

    protected function whereForRecordSetComposite($select, RecordSetInterface $recordSet)
    {
        $uniques = $this->getUniqueCompositeKeys($recordSet);
        $cond = '(' . implode(' = ? AND ', $this->on) . '= ?)';

        // get the first unique composite
        $firstUnique = array_shift($uniques);
        if (! $uniques) {
            // there are no uniques left, which means this is the only one.
            // no need to wrap in parens.
            $select->where($cond, ...$firstUnique);
            return;
        }

        // multiple unique conditions. retain the last unique for later.
        $lastUnique = array_pop($uniques);

        // prefix the first unique with "AND ( -- composite keys" to keep all
        // the uniques within parens
        $select->where(
            '( -- composite keys' . PHP_EOL . '    ' . $cond,
            ...$firstUnique
        );

        // OR the middle uniques within the parens
        foreach ($uniques as $middleUnique) {
            $select->orWhere($cond, ...$middleUnique);
        }

        // suffix the last unique with ") -- composite keys" to end the parens
        $select->orWhere(
            $cond . PHP_EOL . '    ) -- composite keys',
            ...$lastUnique
        );
    }

    protected function getUniqueCompositeKeys(RecordSetInterface $recordSet)
    {
        $uniques = [];
        foreach ($recordSet as $record) {
            $vals = [];
            foreach ($this->on as $nativeCol => $foreignCol) {
                $vals[] = $record->$nativeCol;
            }
            // a pipe, and ASCII 31 ("unit separator").
            // identical composite values should have identical array keys.
            $key = implode("|\x1F", $vals);
            $uniques[$key] = $vals;
        }
        return $uniques;
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
