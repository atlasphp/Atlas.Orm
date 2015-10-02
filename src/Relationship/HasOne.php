<?php
namespace Atlas\Relationship;

use Atlas\Mapper\Related;
use Atlas\Mapper\Record;
use Atlas\Mapper\RecordSet;

class HasOne extends AbstractRelationship
{
    public function stitchIntoRecord(
        Record $nativeRecord,
        callable $custom = null
    ) {
        $this->fix();
        $foreignVal = $nativeRecord->{$this->nativeCol};
        $foreignRecord = $this->foreignSelect($foreignVal, $custom)->fetchRecord();
        $nativeRecord->{$this->name} = $foreignRecord;
    }

    public function stitchIntoRecordSet(
        RecordSet $nativeRecordSet,
        callable $custom = null
    ) {
        $this->fix();

        $foreignVals = $this->getUniqueVals($nativeRecordSet, $this->nativeCol);
        $foreignRecords = $this->groupRecordSets(
            $this->foreignSelect($foreignVals, $custom)->fetchRecordSet(),
            $this->foreignCol
        );

        foreach ($nativeRecordSet as $nativeRecord) {
            $key = $nativeRecord->{$this->nativeCol};
            if (isset($foreignRecords[$key])) {
                $foreignRecord = $foreignRecords[$key][0];
            } else {
                $foreignRecord = $this->getMissing();
            }
            $nativeRecord->{$this->name} = $foreignRecord;
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
