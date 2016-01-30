<?php
namespace Atlas\Orm\Relationship;

use Atlas\Orm\Exception;
use Atlas\Orm\Mapper\RecordInterface;
use Atlas\Orm\Mapper\RecordSetInterface;

class ManyToMany extends AbstractRelationship
{
    public function throughNativeKey($throughNativeKey)
    {
        $this->throughNativeKey = $throughNativeKey;
        return $this;
    }

    public function throughForeignKey($throughForeignKey)
    {
        $this->throughForeignKey = $throughForeignKey;
        return $this;
    }

    protected function fixThroughNativeKey()
    {
        if ($this->throughNativeKey) {
            return;
        }

        $primaryKey = $this->nativeMapper->getTable()->getPrimaryKey();
        $primaryCol = $primaryKey[0];
        $this->throughNativeKey($primaryCol);
    }

    protected function fixThroughForeignKey()
    {
        if ($this->throughForeignKey) {
            return;
        }

        $primaryKey = $this->foreignMapper->getTable()->getPrimaryKey();
        $primaryCol = $primaryKey[0];
        $this->throughForeignKey($primaryCol);
    }

    protected function fixForeignKey()
    {
        if ($this->foreignKey) {
            return;
        }

        $primaryKey = $this->foreignMapper->getTable()->getPrimaryKey();
        $primaryCol = $primaryKey[0];
        $this->foreignKey($primaryCol);
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
        $foreignVals = $this->getUniqueVals($throughRecordSet, $this->throughForeignKey);
        $foreignRecordSet = $this->fetchForeignRecordSet($foreignVals, $custom);
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
                $this->getUniqueVals($throughRecordSet, $this->throughForeignKey)
            );
        }
        $foreignVals = array_unique($foreignVals);

        $foreignRecordSet = $this->fetchForeignRecordSet($foreignVals, $custom);

        foreach ($nativeRecordSet as $nativeRecord) {
            $throughRecordSet = $nativeRecord->{$this->throughName};
            $vals = $this->getUniqueVals($throughRecordSet, $this->throughForeignKey);
            $nativeRecord->{$this->name} = $this->extractRecordSet(
                $foreignRecordSet,
                $this->foreignKey,
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
