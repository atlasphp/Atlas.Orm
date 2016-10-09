<?php
namespace Atlas\Orm\Relationship;

use Atlas\Orm\Mapper\RecordInterface;

class OneToMany extends AbstractRelationship
{
    public function stitchIntoRecords(
        array $nativeRecords,
        callable $custom = null
    ) {
        $this->fix();

        $select = $this->selectForRecords($nativeRecords, $custom);
        $foreignRecordsArray = $select->fetchRecordsArray();

        foreach ($nativeRecords as $nativeRecord) {
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
