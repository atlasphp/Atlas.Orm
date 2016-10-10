<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Orm\Relationship;

use Atlas\Orm\Exception;
use Atlas\Orm\Mapper\MapperLocator;
use Atlas\Orm\Mapper\RecordInterface;

/**
 *
 * Defines a many-to-many relationship.
 *
 * @package atlas/orm
 *
 */
class ManyToMany extends AbstractRelationship
{
    /**
     * @inheritdoc
     */
    protected function initializeOn()
    {
        foreach ($this->foreignMapper->getTable()->getPrimaryKey() as $col) {
            $this->on[$col] = $col;
        }
    }

    /**
     * @inheritdoc
     */
    public function stitchIntoRecords(
        array $nativeRecords,
        callable $custom = null
    ) {
        if (! $nativeRecords) {
            return;
        }

        $this->initialize();

        $throughRecords = $this->getThroughRecords($nativeRecords);
        $foreignRecords = $this->fetchForeignRecords($throughRecords, $custom);
        foreach ($nativeRecords as $nativeRecord) {
            $this->stitchIntoRecord($nativeRecord, $foreignRecords);
        }
    }

    /**
     * @inheritdoc
     */
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

    /**
     *
     * Given an array of native Record objects, finds all the association table
     * (join table) Records in their "through" fields.
     *
     */
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
            foreach ($nativeRecord->{$this->throughName} as $throughRecord) {
                $throughRecords[] = $throughRecord;
            }
        }

        return $throughRecords;
    }

    /**
     *
     * Given a native Record and an array of foreign Record objects, finds
     * which foreign Record objects match the native, using the "through"
     * RecordSet on the native Record.
     *
     * @param RecordInterface $nativeRecord The native Record.
     *
     * @param array $foreignRecords The array of candidate foreign Record
     * matches.
     *
     * @return array An array of matching Foreign record objects.
     *
     */
    protected function getMatches(RecordInterface $nativeRecord, array $foreignRecords)
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
