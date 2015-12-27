<?php
namespace Atlas\Orm\Relationship;

use Atlas\Orm\Mapper\Related;
use Atlas\Orm\Mapper\Record;
use Atlas\Orm\Mapper\RecordSet;

class OneToMany extends AbstractRelationship
{
    public function stitchIntoRecord(
        Record $nativeRecord,
        callable $custom = null
    ) {
        $this->fix();
        $foreignVal = $nativeRecord->{$this->nativeCol};
        $foreignRecordSet = $this->foreignSelect($foreignVal, $custom)->fetchRecordSet();
        $nativeRecord->{$this->name} = $foreignRecordSet;
    }

    public function stitchIntoRecordSet(
        RecordSet $nativeRecordSet,
        callable $custom = null
    ) {
        $this->fix();

        $foreignVals = $this->getUniqueVals($nativeRecordSet, $this->nativeCol);
        $foreignRecordSets = $this->groupRecordSets(
            $this->foreignSelect($foreignVals, $custom)->fetchRecordSet(),
            $this->foreignCol
        );

        foreach ($nativeRecordSet as $nativeRecord) {
            $foreignRecordSet = [];
            $key = $nativeRecord->{$this->nativeCol};
            if (isset($foreignRecordSets[$key])) {
                $foreignRecordSet = $foreignRecordSets[$key];
            }
            $nativeRecord->{$this->name} = $foreignRecordSet;
        }
    }
}
