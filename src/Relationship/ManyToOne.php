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
class ManyToOne extends OneToOne
{
    /**
     *
     * Initializes the `$on` property for the relationship.
     *
     */
    protected function initializeOn() : void
    {
        foreach ($this->foreignMapper->getTable()->getPrimaryKey() as $col) {
            $this->on[$col] = $col;
        }
    }

    /**
     *
     * Given a native Record, sets the related foreign Record values into the
     * native Record.
     *
     * @param RecordInterface $nativeRecord The native Record to work with.
     *
     */
    public function fixNativeRecordKeys(RecordInterface $nativeRecord) : void
    {
        $foreignRecord = $nativeRecord->{$this->name};
        if (! $foreignRecord instanceof RecordInterface) {
            return;
        }

        $this->initialize();

        foreach ($this->getOn() as $nativeField => $foreignField) {
            $nativeRecord->$nativeField = $foreignRecord->$foreignField;
        }
    }

    /**
     *
     * Given a native Record, persists the related foreign Records.
     *
     * @param RecordInterface $nativeRecord The native Record being persisted.
     *
     * @param SplObjectStorage $tracker Tracks which Record objects have been
     * operated on, to prevent infinite recursion.
     *
     */
    public function persistForeign(RecordInterface $nativeRecord, SplObjectStorage $tracker) : void
    {
        $this->persistForeignRecord($nativeRecord, $tracker);
    }
}
