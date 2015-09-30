<?php
namespace Atlas\Relationship;

use Atlas\Mapper\Related;
use Atlas\Mapper\RelatedSet;
use Atlas\Table\Row;
use Atlas\Table\RowSet;

class HasOne extends AbstractRelationship
{
    public function fetchForRow(
        Row $nativeRow,
        Related $related,
        callable $custom = null
    ) {
        $this->fix();
        $foreignVal = $nativeRow->{$this->nativeCol};
        $foreignRecord = $this->foreignSelect($foreignVal, $custom)->fetchRecord();
        $related->{$this->name} = $foreignRecord;
    }

    public function fetchForRowSet(
        RowSet $nativeRowSet,
        RelatedSet $relatedSet,
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
            $related = $relatedSet->get($nativeRow->getPrimaryVal());
            $related->{$this->name} = $foreignRecord;
        }
    }

    protected function getMissing()
    {
        if ($this->orNone) {
            return false;
        }

        return $this->foreignMapper->newRecord([]);
    }

    protected function fixEmptyValue()
    {
        $this->emptyValue = false;
    }

    protected function fixForeignClass()
    {
        $this->foreignClass = $this->foreignMapper->getRecordClass();
    }
}
