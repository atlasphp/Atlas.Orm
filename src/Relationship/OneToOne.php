<?php
namespace Atlas\Orm\Relationship;

use Atlas\Orm\Mapper\RecordInterface;
use Atlas\Orm\Mapper\RecordSetInterface;

class OneToOne extends AbstractRelationship
{
    public function stitchIntoRecord(
        RecordInterface $nativeRecord,
        callable $custom = null
    ) {
        $this->fix();
        $foreignVal = $nativeRecord->{$this->nativeKey};
        $foreignRecord = $this->foreignSelect($foreignVal, $custom)->fetchRecord();
        $nativeRecord->{$this->name} = $foreignRecord;
    }

    public function stitchIntoRecordSet(
        RecordSetInterface $nativeRecordSet,
        callable $custom = null
    ) {
        $this->fix();

        $foreignVals = $this->getUniqueVals($nativeRecordSet, $this->nativeKey);
        $foreignRecords = $this->groupRecordSets(
            $this->foreignSelect($foreignVals, $custom)->fetchRecordSet(),
            $this->foreignKey
        );

        foreach ($nativeRecordSet as $nativeRecord) {
            $foreignRecord = false;
            $key = $nativeRecord->{$this->nativeKey};
            if (isset($foreignRecords[$key])) {
                $foreignRecord = $foreignRecords[$key][0];
            }
            $nativeRecord->{$this->name} = $foreignRecord;
        }
    }
}
