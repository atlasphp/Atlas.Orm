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
        $select = $this->selectForRecord($nativeRecord, $custom);
        $nativeRecord->{$this->name} = $select->fetchRecordSet();
    }

    public function stitchIntoRecordSet(
        RecordSetInterface $nativeRecordSet,
        callable $custom = null
    ) {
        $this->fix();

        $select = $this->selectForRecordSet($nativeRecordSet, $custom);
        $foreignRecordsArray = $select->fetchRecordsArray();

        foreach ($nativeRecordSet as $nativeRecord) {
            $nativeRecord->{$this->name} = [];
            $matches = [];
            foreach ($foreignRecordsArray as $foreignRecord) {
                if ($this->recordsMatch($nativeRecord, $foreignRecord)) {
                    $matches[] = $foreignRecord;
                }
            }
            if ($matches) {
                $nativeRecord->{$this->name} = $this->foreignMapper->newRecordSet($matches);
            }
        }
    }
}
