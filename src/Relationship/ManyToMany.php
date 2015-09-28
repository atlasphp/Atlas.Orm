<?php
namespace Atlas\Relationship;

use Atlas\Exception;
use Atlas\Mapper\Mapper;
use Atlas\Mapper\MapperLocator;
use Atlas\Table\Row;
use Atlas\Table\RowSet;

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
        array &$related,
        callable $custom = null
    ) {
        $this->fix();

        // make sure the "through" relation is loaded already
        if (! isset($related[$this->throughName])) {
            throw new Exception("Cannot fetch '{$this->name}' relation without '{$this->throughName}' relation");
        }

        $throughRecordSet = $related[$this->throughName];
        $foreignVals = $this->getUniqueVals($throughRecordSet, $this->throughForeignCol);
        $foreign = $this->foreignSelect($foreignVals, $custom)->fetchRecordSet();
        $related[$this->name] = $foreign;
    }

    public function fetchForRowSet(
        RowSet $nativeRowSet,
        array &$relatedSet,
        callable $custom = null
    ) {
        $this->fix();

        // this is a bit hackish.
        // the "through" relation should be loaded for everything, so if even
        // one is loaded, then all the others ought to have been loaded too.
        $related = current($relatedSet);
        if (! isset($related[$this->throughName])) {
            throw new Exception("Cannot fetch '{$this->name}' relation without '{$this->throughName}' relation");
        }

        $foreignVals = [];
        foreach ($nativeRowSet as $nativeRow) {
            $primaryVal = $nativeRow->getPrimaryVal();
            $throughRecordSet = $relatedSet[$primaryVal][$this->throughName];
            $foreignVals = array_merge(
                $foreignVals,
                $this->getUniqueVals($throughRecordSet, $this->throughForeignCol)
            );
        }
        $foreignVals = array_unique($foreignVals);

        $foreignRecordSet = $this->foreignSelect($foreignVals, $custom)->fetchRecordSet();

        foreach ($nativeRowSet as $nativeRow) {
            $primaryVal = $nativeRow->getPrimaryVal();
            $throughRecordSet = $relatedSet[$primaryVal][$this->throughName];
            $vals = $this->getUniqueVals($throughRecordSet, $this->throughForeignCol);
            $relatedSet[$primaryVal][$this->name] = $this->extractRecordSet(
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
            return [];
        }

        return $extracted;
    }
}
