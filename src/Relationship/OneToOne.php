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
class OneToOne extends AbstractRelationship
{
    /**
     * @inheritdoc
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
}
