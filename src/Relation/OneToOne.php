<?php
namespace Atlas\Orm\Relation;

use Atlas\Orm\Mapper\Related;
use Atlas\Orm\Mapper\Record;
use Atlas\Orm\Mapper\RecordSet;

class OneToOne extends AbstractRelation
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
            $foreignRecord = false;
            $key = $nativeRecord->{$this->nativeCol};
            if (isset($foreignRecords[$key])) {
                $foreignRecord = $foreignRecords[$key][0];
            }
            $nativeRecord->{$this->name} = $foreignRecord;
        }
    }
}
