<?php
namespace Atlas\Relationship;

use Atlas\Mapper\Mapper;
use Atlas\Mapper\MapperLocator;
use Atlas\Table\Row;
use Atlas\Table\RowSet;

class HasOne extends AbstractRelationship
{
    public function fetchForRow(
        Row $nativeRow,
        array &$related,
        callable $custom = null
    ) {
        $this->fix();
        $foreignVal = $nativeRow->{$this->nativeCol};
        $foreign = $this->foreignSelect($foreignVal, $custom)->fetchRecord();
        $related[$this->name] = $foreign;
    }

    public function fetchForRowSet(
        RowSet $nativeRowSet,
        array &$relatedSet,
        callable $custom = null
    ) {
        $this->fix();

        $foreignVals = $this->getUniqueVals($nativeRowSet, $this->nativeCol);
        $foreignRecords = $this->groupRecordSets(
            $this->foreignSelect($foreignVals, $custom)->fetchRecordSet(),
            $this->foreignCol
        );

        foreach ($nativeRowSet as $nativeRow) {
            $key = $nativeRow->{$this->nativeCol};
            if (isset($foreignRecords[$key])) {
                $foreignRecord = $foreignRecords[$key][0];
            } else {
                $foreignRecord = $this->getMissing();
            }
            $relatedSet[$nativeRow->getPrimaryVal()][$this->name] = $foreignRecord;
        }
    }

    protected function getMissing()
    {
        if ($this->orNone) {
            return false;
        }

        return $this->foreignMapper->newRecord([]);
    }
}
