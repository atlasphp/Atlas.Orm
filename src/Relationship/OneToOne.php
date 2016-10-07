<?php
namespace Atlas\Orm\Relationship;

use Atlas\Orm\Mapper\RecordInterface;
use Atlas\Orm\Mapper\RecordSetInterface;

class OneToOne extends AbstractRelationship
{
    protected function stitchIntoRecord(
        RecordInterface $nativeRecord,
        callable $custom = null
    ) {
        $this->fix();
        $select = $this->selectForRecord($nativeRecord, $custom);
        $nativeRecord->{$this->name} = $select->fetchRecord();
    }

    protected function stitchIntoRecordSet(
        RecordSetInterface $nativeRecordSet,
        callable $custom = null
    ) {
        $this->fix();

        $select = $this->selectForRecordSet($nativeRecordSet, $custom);
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
