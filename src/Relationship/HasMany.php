<?php
namespace Atlas\Relationship;

use Atlas\Mapper\Related;
use Atlas\Mapper\Record;
use Atlas\Mapper\RecordSet;

class HasMany extends AbstractRelationship
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
            $key = $nativeRecord->{$this->nativeCol};
            if (isset($foreignRecordSets[$key])) {
                $foreignRecordSet = $foreignRecordSets[$key];
            } else {
                $foreignRecordSet = $this->getMissing();
            }
            $nativeRecord->{$this->name} = $foreignRecordSet;
        }
    }

    protected function getMissing()
    {
        if ($this->orNone) {
            return array();
        }

        return $this->foreignMapper->newRecordSet();
    }

    protected function fixEmptyValue()
    {
        $this->emptyValue = [];
    }

    protected function fixForeignClass()
    {
        $this->foreignClass = $this->foreignMapper->getRecordSetClass();
    }
}
