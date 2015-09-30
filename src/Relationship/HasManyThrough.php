<?php
namespace Atlas\Relationship;

use Atlas\Exception;
use Atlas\Mapper\Related;
use Atlas\Mapper\RelatedSet;
use Atlas\Table\Row;
use Atlas\Table\RowSet;

class HasManyThrough extends AbstractRelationship
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

        $this->throughNativeCol = $this->nativeMapper->getTable()->getPrimary();
    }

    protected function fixThroughForeignCol()
    {
        if ($this->throughForeignCol) {
            return;
        }

        $this->throughForeignCol = $this->foreignMapper->getTable()->getPrimary();
    }

    protected function fixForeignCol()
    {
        if ($this->foreignCol) {
            return;
        }

        $this->foreignCol = $this->foreignMapper->getTable()->getPrimary();
    }

    public function fetchForRow(
        Row $nativeRow,
        Related $related,
        callable $custom = null
    ) {
        $this->fix();

        // make sure the "through" relation is loaded already
        if (! isset($related->{$this->throughName})) {
            throw new Exception("Cannot fetch '{$this->name}' relation without '{$this->throughName}' relation");
        }

        $throughRecordSet = $related->{$this->throughName};
        $foreignVals = $this->getUniqueVals($throughRecordSet, $this->throughForeignCol);
        $foreignRecordSet = $this->foreignSelect($foreignVals, $custom)->fetchRecordSet();
        $related->{$this->name} = $foreignRecordSet;
    }

    public function fetchForRowSet(
        RowSet $nativeRowSet,
        RelatedSet $relatedSet,
        callable $custom = null
    ) {
        $this->fix();

        // this hackish in two ways:
        // 1. the "through" relation should be loaded for everything, so if even
        // one is loaded, then all the others ought to have been loaded too.
        // 2. reset() returns the array from the ArrayIterator. weird.
        $array = reset($nativeRowSet);
        $primary = $array[0]->getPrimaryVal();
        $related = $relatedSet->get($primary);
        if (! isset($related->{$this->throughName})) {
            throw new Exception("Cannot fetch '{$this->name}' relation without '{$this->throughName}' relation");
        }

        $foreignVals = [];
        foreach ($nativeRowSet as $nativeRow) {
            $primaryVal = $nativeRow->getPrimaryVal();
            $throughRecordSet = $relatedSet->get($primaryVal)->{$this->throughName};
            $foreignVals = array_merge(
                $foreignVals,
                $this->getUniqueVals($throughRecordSet, $this->throughForeignCol)
            );
        }
        $foreignVals = array_unique($foreignVals);

        $foreignRecordSet = $this->foreignSelect($foreignVals, $custom)->fetchRecordSet();

        foreach ($nativeRowSet as $nativeRow) {
            $primaryVal = $nativeRow->getPrimaryVal();
            $related = $relatedSet->get($primaryVal);
            $throughRecordSet = $related->{$this->throughName};
            $vals = $this->getUniqueVals($throughRecordSet, $this->throughForeignCol);
            $related->{$this->name} = $this->extractRecordSet(
                $foreignRecordSet,
                $this->foreignCol,
                $vals
            );
        }
    }

    protected function extractRecordSet($recordSet, $field, $vals)
    {
        $vals = (array) $vals;
        $extracted = $this->foreignMapper->newRecordSet([]);
        foreach ($recordSet as $record) {
            if (in_array($record->$field, $vals)) {
                $extracted[] = $record;
            }
        }

        if ($extracted->isEmpty()) {
            return $this->getMissing();
        }

        return $extracted;
    }


    protected function getMissing()
    {
        if ($this->orNone) {
            return array();
        }

        return $this->foreignMapper->newRecordSet();
    }

    protected function fixEmptyValue()
    {
        $this->emptyValue = [];
    }

    protected function fixForeignClass()
    {
        $this->foreignClass = $this->foreignMapper->getRecordSetClass();
    }
}
