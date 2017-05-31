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
use SplObjectStorage;

/**
 *
 * Defines a one-to-one relationship.
 *
 * @package atlas/orm
 *
 */
class OneToOne extends AbstractRelationship
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
        $nativeRecord->{$this->name} = false;
        foreach ($foreignRecords as $foreignRecord) {
            if ($this->recordsMatch($nativeRecord, $foreignRecord)) {
                $nativeRecord->{$this->name} = $foreignRecord;
            }
        }
    }

    public function fixNativeRecordKeys(RecordInterface $nativeRecord)
    {
        // do nothing
    }

    public function fixForeignRecordKeys(RecordInterface $nativeRecord)
    {
        $foreignRecord = $nativeRecord->{$this->name};
        if (! $foreignRecord instanceof RecordInterface) {
            return;
        }

        $this->initialize();

        foreach ($this->getOn() as $nativeField => $foreignField) {
            $foreignRecord->$foreignField = $nativeRecord->$nativeField;
        }
    }

    public function persistForeign(RecordInterface $nativeRecord, SplObjectStorage $tracker)
    {
        $this->persistForeignRecord($nativeRecord, $tracker);
    }
}
