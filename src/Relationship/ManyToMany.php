<?php
namespace Atlas\Relationship;

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
        Row $row,
        array &$related,
        callable $custom = null
    ) {
        $this->fix();
        $throughRecordSet = $related[$this->throughName];
        $foreignVals = $this->getUniqueVals($throughRecordSet, $this->throughForeignCol);
        $foreign = $this->foreignSelect($foreignVals, $custom)->fetchRecordSet();
        $related[$this->name] = $foreign;
    }

    public function fetchForRowSet(
        RowSet $rowSet,
        array &$relatedSet,
        callable $custom = null
    ) {
        $this->fix();

        $foreignVals = [];
        foreach ($rowSet as $row) {
            $primaryVal = $row->getPrimaryVal();
            $throughRecordSet = $relatedSet[$primaryVal][$this->throughName];
            $foreignVals = array_merge(
                $foreignVals,
                $this->getUniqueVals($throughRecordSet, $this->throughForeignCol)
            );
        }
        $foreignVals = array_unique($foreignVals);

        $foreignRecordSet = $this->foreignSelect($foreignVals, $custom)->fetchRecordSet();

        foreach ($rowSet as $row) {
            $primaryVal = $row->getPrimaryVal();
            $throughRecordSet = $relatedSet[$primaryVal][$this->throughName];
            $vals = $this->getUniqueVals($throughRecordSet, $this->throughForeignCol);
            $relatedSet[$primaryVal][$this->name] = $this->newRecordSetBy(
                $foreignRecordSet,
                $this->foreignCol,
                $vals
            );
        }
    }

    protected function newRecordSetBy($recordSet, $field, $vals)
    {
        $vals = (array) $vals;
        $newRecordSet = $this->foreignMapper->newRecordSet([]);
        foreach ($recordSet as $record) {
            if (in_array($record->$field, $vals)) {
                $newRecordSet[] = $record;
            }
        }

        if ($newRecordSet->isEmpty()) {
            return [];
        }

        // empty array
        return $newRecordSet;
    }
}
