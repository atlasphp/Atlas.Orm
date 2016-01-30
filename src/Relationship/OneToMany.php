<?php
namespace Atlas\Orm\Relationship;

use Atlas\Orm\Mapper\RecordInterface;
use Atlas\Orm\Mapper\RecordSetInterface;

class OneToMany extends AbstractRelationship
{
    public function stitchIntoRecord(
        RecordInterface $nativeRecord,
        callable $custom = null
    ) {
        $this->fix();
        $foreignVal = $nativeRecord->{$this->nativeKey};
        $foreignRecordSet = $this->fetchForeignRecordSet($foreignVal, $custom);
        $nativeRecord->{$this->name} = $foreignRecordSet;
    }

    public function stitchIntoRecordSet(
        RecordSetInterface $nativeRecordSet,
        callable $custom = null
    ) {
        $this->fix();

        $foreignVals = $this->getUniqueVals($nativeRecordSet, $this->nativeKey);
        $foreignRecordSets = $this->groupRecordSets(
            $this->fetchForeignRecordSet($foreignVals, $custom),
            $this->foreignKey
        );

        foreach ($nativeRecordSet as $nativeRecord) {
            $foreignRecordSet = [];
            $key = $nativeRecord->{$this->nativeKey};
            if (isset($foreignRecordSets[$key])) {
                $foreignRecordSet = $foreignRecordSets[$key];
            }
            $nativeRecord->{$this->name} = $foreignRecordSet;
        }
    }
}
