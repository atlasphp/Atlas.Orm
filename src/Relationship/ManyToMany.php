<?php
namespace Atlas\Orm\Relationship;

use Atlas\Orm\Exception;
use Atlas\Orm\Mapper\MapperLocator;
use Atlas\Orm\Mapper\RecordInterface;
use Atlas\Orm\Mapper\RecordSet;
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
        $result = [];
        if ($throughRecordSet instanceof RecordSetInterface) {
            $select = $this->selectForRecordSet($throughRecordSet, $custom);
            $result = $select->fetchRecordSet();
        }

        $nativeRecord->{$this->name} = $result;
    }

    public function stitchIntoRecordSet(
        RecordSetInterface $nativeRecordSet,
        callable $custom = null
    ) {
        $this->fix();

        if ($nativeRecordSet->isEmpty()) {
            return;
        }

        $throughRecordSet = $this->throughRecordSet($nativeRecordSet);
        $select = $this->selectForRecordSet($throughRecordSet, $custom);
        $foreignRecordsArray = $select->fetchRecordsArray();

        foreach ($nativeRecordSet as $nativeRecord) {
            $nativeRecord->{$this->name} = [];
            $matches = $this->getMatches($nativeRecord, $foreignRecordsArray);
            if ($matches) {
                $nativeRecord->{$this->name} = $this->foreignMapper->newRecordSet($matches);
            }
        }
    }

    protected function throughRecordSet(RecordSetInterface $nativeRecordSet)
    {
        // this hackish. the "through" relation should be loaded for everything,
        // so if even one is loaded, all the others ought to have been too.
        $firstNative = $nativeRecordSet[0];
        if (! isset($firstNative->{$this->throughName})) {
            throw Exception::throughRelationNotFetched($this->name, $this->throughName);
        }

        $throughRecordSet = new RecordSet([]);
        foreach ($nativeRecordSet as $nativeRecord) {
            foreach ($nativeRecord->{$this->throughName} as $throughRecord)
            $throughRecordSet[] = $throughRecord;
        }

        return $throughRecordSet;
    }

    protected function getMatches($nativeRecord, $foreignRecordsArray)
    {
        $matches = [];
        foreach ($nativeRecord->{$this->throughName} as $throughRecord) {
            foreach ($foreignRecordsArray as $foreignRecord) {
                if ($this->recordsMatch($throughRecord, $foreignRecord)) {
                    $matches[] = $foreignRecord;
                }
            }
        }
        return $matches;
    }
}
