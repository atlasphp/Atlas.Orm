<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Orm\Relationship;

use Atlas\Orm\Mapper\MapperLocator;
use Atlas\Orm\Mapper\RecordInterface;
use Atlas\Orm\Mapper\RecordSetInterface;

/**
 *
 * __________
 *
 * @package atlas/orm
 *
 */
abstract class AbstractRelationship
{
    protected $mapperLocator;

    protected $name;

    protected $nativeMapperClass;
    protected $nativeMapper;

    protected $foreignMapperClass;
    protected $foreignMapper;
    protected $foreignTable;

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
        $this->foreignTable = $this->foreignMapper->getTable()->getName();
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

    protected function fetchForeignRecords(array $records, $custom)
    {
        $select = $this->foreignMapper->select();
        $this->selectForeignWhere($select, $records);
        if ($custom) {
            $custom($select);
        }
        return $select->fetchRecords();
    }

    protected function selectForeignWhere($select, array $records)
    {
        if (count($this->on) > 1) {
            return $this->selectForeignWhereComposite($select, $records);
        }

        $vals = [];
        $nativeCol = key($this->on);
        foreach ($records as $record) {
            $row = $record->getRow();
            $vals[] = $row->$nativeCol;
        }

        $foreignCol = current($this->on);
        $select->where("{$this->foreignTable}.{$foreignCol} IN (?)", array_unique($vals));
    }

    protected function selectForeignWhereComposite($select, array $records)
    {
        $uniques = $this->getUniqueCompositeKeys($records);
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

    protected function getUniqueCompositeKeys(array $records)
    {
        $uniques = [];
        foreach ($records as $record) {
            $row = $record->getRow();
            $vals = [];
            foreach ($this->on as $nativeCol => $foreignCol) {
                $vals[] = $row->$nativeCol;
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
        $nativeRow = $nativeRecord->getRow();
        $foreignRow = $foreignRecord->getRow();
        foreach ($this->on as $nativeCol => $foreignCol) {
            if ($nativeRow->$nativeCol != $foreignRow->$foreignCol) {
                return false;
            }
        }
        return true;
    }

    public function stitchIntoRecords(
        array $nativeRecords,
        callable $custom = null
    ) {
        if (! $nativeRecords) {
            return;
        }

        $this->fix();

        $foreignRecords = $this->fetchForeignRecords($nativeRecords, $custom);
        foreach ($nativeRecords as $nativeRecord) {
            $this->stitchIntoRecord($nativeRecord, $foreignRecords);
        }
    }

    abstract protected function stitchIntoRecord(
        RecordInterface $nativeRecord,
        array $foreignRecords
    );
}
