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
 * __________
 *
 * @package atlas/orm
 *
 */
class OneToMany extends AbstractRelationship
{
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
}
