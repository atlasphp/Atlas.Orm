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
    protected function initializeOn()
    {
        foreach ($this->foreignMapper->getTable()->getPrimaryKey() as $col) {
            $this->on[$col] = $col;
        }
    }

    public function fixNativeRecordKeys(RecordInterface $nativeRecord)
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

    public function fixForeignRecordKeys(RecordInterface $nativeRecord)
    {
        // do nothing
    }

    public function persist(RecordInterface $nativeRecord)
    {
        // do nothing
    }
}
