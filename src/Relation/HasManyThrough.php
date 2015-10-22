<?php
namespace Atlas\Relation;

use Atlas\Exception;
use Atlas\Mapper\Related;
use Atlas\Mapper\AbstractRecord;
use Atlas\Mapper\AbstractRecordSet;

class HasManyThrough extends AbstractRelation
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

        $this->throughNativeCol($this->nativeMapper->getTable()->getRowFactory()->getPrimary());
    }

    protected function fixThroughForeignCol()
    {
        if ($this->throughForeignCol) {
            return;
        }

        $this->throughForeignCol($this->foreignMapper->getTable()->getRowFactory()->getPrimary());
    }

    protected function fixForeignCol()
    {
        if ($this->foreignCol) {
            return;
        }

        $this->foreignCol($this->foreignMapper->getTable()->getRowFactory()->getPrimary());
    }

    public function stitchIntoRecord(
        AbstractRecord $nativeRecord,
        callable $custom = null
    ) {
        $this->fix();

        // make sure the "through" relation is loaded already
        if (! isset($nativeRecord->{$this->throughName})) {
            throw new Exception("Cannot fetch '{$this->name}' relation without '{$this->throughName}' relation");
        }

        $throughRecordSet = $nativeRecord->{$this->throughName};
        $foreignVals = $this->getUniqueVals($throughRecordSet, $this->throughForeignCol);
        $foreignRecordSet = $this->foreignSelect($foreignVals, $custom)->fetchRecordSet();
        $nativeRecord->{$this->name} = $foreignRecordSet;
    }

    public function stitchIntoRecordSet(
        AbstractRecordSet $nativeRecordSet,
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
            throw new Exception("Cannot fetch '{$this->name}' relation without '{$this->throughName}' relation");
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
