<?php
namespace Atlas\Orm\Relationship;

use Atlas\Orm\Exception;
use Atlas\Orm\Mapper\RecordInterface;
use Atlas\Orm\Mapper\RecordSetInterface;

class ManyToMany extends AbstractRelationship
{
    public function throughNativeCol($throughNativeCol)
    {
        $this->throughNativeCol = $throughNativeCol;
        return $this;
    }

    public function throughForeignCol($throughForeignCol)
    {
        $this->throughForeignCol = $throughForeignCol;
        return $this;
    }

    protected function fixThroughNativeCol()
    {
        if ($this->throughNativeCol) {
            return;
        }

        $primaryKey = (array) $this->nativeMapper->getTable()->getPrimaryKey();
        $primaryCol = $primaryKey[0];
        $this->throughNativeCol($primaryCol);
    }

    protected function fixThroughForeignCol()
    {
        if ($this->throughForeignCol) {
            return;
        }

        $primaryKey = (array) $this->foreignMapper->getTable()->getPrimaryKey();
        $primaryCol = $primaryKey[0];
        $this->throughForeignCol($primaryCol);
    }

    protected function fixForeignCol()
    {
        if ($this->foreignCol) {
            return;
        }

        $primaryKey = (array) $this->foreignMapper->getTable()->getPrimaryKey();
        $primaryCol = $primaryKey[0];
        $this->foreignCol($primaryCol);
    }

    public function stitchIntoRecord(
        RecordInterface $nativeRecord,
        callable $custom = null
    ) {
        $this->fix();

        // make sure the "through" relation is loaded already
        if (! isset($nativeRecord->{$this->throughName})) {
            throw Exception::throughRelationNotFetched($this->name, $this->throughName);
        }

        $throughRecordSet = $nativeRecord->{$this->throughName};
        $foreignVals = $this->getUniqueVals($throughRecordSet, $this->throughForeignCol);
        $foreignRecordSet = $this->foreignSelect($foreignVals, $custom)->fetchRecordSet();
        $nativeRecord->{$this->name} = $foreignRecordSet;
    }

    public function stitchIntoRecordSet(
        RecordSetInterface $nativeRecordSet,
        callable $custom = null
    ) {
        $this->fix();

        if ($nativeRecordSet->isEmpty()) {
            return;
        }

        // this hackish. the "through" relation should be loaded for everything,
        // so if even one is loaded, all the others ought to have been too.
        $firstNative = $nativeRecordSet[0];
        if (! isset($firstNative->{$this->throughName})) {
            throw Exception::throughRelationNotFetched($this->name, $this->throughName);
        }

        $foreignVals = [];
        foreach ($nativeRecordSet as $nativeRecord) {
            $throughRecordSet = $nativeRecord->{$this->throughName};
            $foreignVals = array_merge(
                $foreignVals,
                $this->getUniqueVals($throughRecordSet, $this->throughForeignCol)
            );
        }
        $foreignVals = array_unique($foreignVals);

        $foreignRecordSet = $this->foreignSelect($foreignVals, $custom)->fetchRecordSet();

        foreach ($nativeRecordSet as $nativeRecord) {
            $throughRecordSet = $nativeRecord->{$this->throughName};
            $vals = $this->getUniqueVals($throughRecordSet, $this->throughForeignCol);
            $nativeRecord->{$this->name} = $this->extractRecordSet(
                $foreignRecordSet,
                $this->foreignCol,
                $vals
            );
        }
    }

    protected function extractRecordSet($recordSet, $field, $vals)
    {
        $vals = (array) $vals;

        $records = [];
        foreach ($recordSet as $record) {
            if (in_array($record->$field, $vals)) {
                $records[] = $record;
            }
        }

        if ($records) {
            return $this->foreignMapper->newRecordSet($records);
        }

        return [];
    }
}
