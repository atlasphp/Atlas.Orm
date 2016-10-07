<?php
namespace Atlas\Orm\Relationship;

use Atlas\Orm\Mapper\RecordInterface;
use Atlas\Orm\Mapper\RecordSetInterface;

class OneToOne extends AbstractRelationship
{
    public function stitchIntoRecords(
        /* traversable */ $nativeRecordSet,
        callable $custom = null
    ) {
        $this->fix();

        $select = $this->selectForRecords($nativeRecordSet, $custom);
        $foreignRecordsArray = $select->fetchRecordsArray();

        foreach ($nativeRecordSet as $nativeRecord) {
            $nativeRecord->{$this->name} = false;
            foreach ($foreignRecordsArray as $foreignRecord) {
                if ($this->recordsMatch($nativeRecord, $foreignRecord)) {
                    $nativeRecord->{$this->name} = $foreignRecord;
                }
            }
        }
    }
}
