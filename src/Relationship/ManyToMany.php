<?php
namespace Atlas\Orm\Relationship;

use Atlas\Orm\Exception;
use Atlas\Orm\Mapper\MapperLocator;
use Atlas\Orm\Mapper\RecordInterface;

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

    public function stitchIntoRecords(
        array $nativeRecords,
        callable $custom = null
    ) {
        if (! $nativeRecords) {
            return;
        }

        $this->fix();

        $throughRecords = $this->getThroughRecords($nativeRecords);
        $foreignRecords = $this->fetchForeignRecords($throughRecords, $custom);
        foreach ($nativeRecords as $nativeRecord) {
            $this->stitchIntoRecord($nativeRecord, $foreignRecords);
        }
    }

    protected function stitchIntoRecord(
        RecordInterface $nativeRecord,
        array $foreignRecords
    ) {
        $nativeRecord->{$this->name} = [];
        $matches = $this->getMatches($nativeRecord, $foreignRecords);
        if ($matches) {
            $nativeRecord->{$this->name} = $this->foreignMapper->newRecordSet($matches);
        }
    }

    protected function getThroughRecords(array $nativeRecords)
    {
        // this hackish. the "through" relation should be loaded for everything,
        // so if even one is loaded, all the others ought to have been too.
        $firstNative = $nativeRecords[0];
        if (! isset($firstNative->{$this->throughName})) {
            throw Exception::throughRelationNotFetched($this->name, $this->throughName);
        }

        $throughRecords = [];
        foreach ($nativeRecords as $nativeRecord) {
            foreach ($nativeRecord->{$this->throughName} as $throughRecord)
            $throughRecords[] = $throughRecord;
        }

        return $throughRecords;
    }

    protected function getMatches($nativeRecord, $foreignRecords)
    {
        $matches = [];
        foreach ($nativeRecord->{$this->throughName} as $throughRecord) {
            foreach ($foreignRecords as $foreignRecord) {
                if ($this->recordsMatch($throughRecord, $foreignRecord)) {
                    $matches[] = $foreignRecord;
                }
            }
        }
        return $matches;
    }
}
