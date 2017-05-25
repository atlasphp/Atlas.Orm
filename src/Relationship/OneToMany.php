<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Orm\Relationship;

use Atlas\Orm\Mapper\RecordInterface;
use Atlas\Orm\Mapper\RecordSetInterface;

/**
 *
 * Defines a one-to-many relationship.
 *
 * @package atlas/orm
 *
 */
class OneToMany extends AbstractRelationship
{
    /**
     *
     * Stitches one or more foreign Record objects into a native Record.
     *
     * @param RecordInterface $nativeRecord The native Record.
     *
     * @param array $foreignRecords All the foreign Record objects fetched for
     * the relationship.
     *
     */
    protected function stitchIntoRecord(
        RecordInterface $nativeRecord,
        array $foreignRecords
    ) {
        $nativeRecord->{$this->name} = [];
        $matches = [];
        foreach ($foreignRecords as $foreignRecord) {
            if ($this->recordsMatch($nativeRecord, $foreignRecord)) {
                $matches[] = $foreignRecord;
            }
        }
        if ($matches) {
            $nativeRecord->{$this->name} = $this->foreignMapper->newRecordSet($matches);
        }
    }

    public function fixNativeRecordKeys(RecordInterface $nativeRecord)
    {
        // do nothing
    }

    public function fixForeignRecordKeys(RecordInterface $nativeRecord)
    {
        $foreignRecordSet = $nativeRecord->{$this->name};
        if (! $foreignRecordSet instanceof RecordSetInterface) {
            return;
        }

        $this->initialize();

        foreach ($foreignRecordSet as $foreignRecord) {
            foreach ($this->getOn() as $nativeField => $foreignField) {
                $foreignRecord->$foreignField = $nativeRecord->$nativeField;
            }
        }
    }
}
