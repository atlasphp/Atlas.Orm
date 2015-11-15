<?php
namespace Atlas\Orm\Relation;

use Atlas\Orm\Mapper\Related;
use Atlas\Orm\Mapper\AbstractRecord;
use Atlas\Orm\Mapper\AbstractRecordSet;

class OneToMany extends AbstractRelation
{
    public function stitchIntoRecord(
        AbstractRecord $nativeRecord,
        callable $custom = null
    ) {
        $this->fix();
        $foreignVal = $nativeRecord->{$this->nativeCol};
        $foreignRecordSet = $this->foreignSelect($foreignVal, $custom)->fetchRecordSet();
        $nativeRecord->{$this->name} = $foreignRecordSet;
    }

    public function stitchIntoRecordSet(
        AbstractRecordSet $nativeRecordSet,
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
