<?php
namespace Atlas\Orm\Relationship;

use Atlas\Orm\Exception;
use Atlas\Orm\Mapper\MapperLocator;
use Atlas\Orm\Mapper\RecordInterface;
use Atlas\Orm\Mapper\RecordSetInterface;

class ManyToMany extends AbstractRelationship
{
    protected function fixOn()
    {
        if ($this->on) {
            return;
        }

        foreach ($this->foreignMapper->getTable()->getPrimaryKey() as $col) {
            $this->on[$col] = $col;
        }
    }

    public function stitchIntoRecord(
        RecordInterface $nativeRecord,
        callable $custom = null
    ) {
        $this->fix();

        // make sure the "through" relation is loaded already
        if (! isset($nativeRecord->{$this->throughName})) {
            throw Exception::throughRelationNotFetched($this->name, $this->throughName);
        }

        $throughRecordSet = $nativeRecord->{$this->throughName};
        $select = $this->selectForRecordSet($throughRecordSet, $custom);
        $nativeRecord->{$this->name} = $select->fetchRecordSet();
    }

    public function stitchIntoRecordSet(
        RecordSetInterface $nativeRecordSet,
        callable $custom = null
    ) {
        $this->fix();

        if ($nativeRecordSet->isEmpty()) {
            return;
        }

        // this hackish. the "through" relation should be loaded for everything,
        // so if even one is loaded, all the others ought to have been too.
        $firstNative = $nativeRecordSet[0];
        if (! isset($firstNative->{$this->throughName})) {
            throw Exception::throughRelationNotFetched($this->name, $this->throughName);
        }

        $select = $this->foreignMapper->select();
        foreach ($nativeRecordSet as $nativeRecord) {
            foreach ($nativeRecord->{$this->throughName} as $throughRecordSet) {
                foreach ($throughRecordSet as $throughRecord) {
                    list($cond, $vals) = $this->whereCondVals($throughRecord);
                    $select->orWhere($cond, $vals);
                }
            }
        }
        if ($custom) {
            $custom($select);
        }

        $foreignRecordsArray = $select->fetchRecordsArray();

        foreach ($nativeRecordSet as $nativeRecord) {
            $nativeRecord->{$this->name} = [];
            $matches = [];
            foreach ($nativeRecord->{$this->throughName} as $throughRecord) {
                foreach ($foreignRecordsArray as $foreignRecord) {
                    if ($this->recordsMatch($throughRecord, $foreignRecord)) {
                        $matches[] = $foreignRecord;
                    }
                }
            }
            if ($matches) {
                $nativeRecord->{$this->name} = $this->foreignMapper->newRecordSet($matches);
            }
        }
    }
}
