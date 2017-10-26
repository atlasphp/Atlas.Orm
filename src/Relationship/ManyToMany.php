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
use Atlas\Orm\Mapper\RecordInterface;
use SplObjectStorage;

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
     * Given an array of native Record objects, stitches the foreign relateds
     * into them as fields under the relationship name.
     *
     * @param array $nativeRecords The native Record objects.
     *
     * @param callable $custom A callable in the form `function (MapperSelect $select)`
     * to modify the foreign MapperSelect statement.
     *
     */
    public function stitchIntoRecords(
        array $nativeRecords,
        callable $custom = null
    ) : void {
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
    ) : void {
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
     * @param array $nativeRecords Find the "through" Record objects on these
     * native Record objects.
     *
     * @return array
     *
     */
    protected function getThroughRecords(array $nativeRecords) : array
    {
        // this hackish. the "through" relation should be loaded for everything,
        // so if even one is loaded, all the others ought to have been too.
        $firstNative = $nativeRecords[0];
        if (! isset($firstNative->{$this->throughName})) {
            throw Exception::throughRelationshipNotFetched($this->name, $this->throughName);
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
     * @return array An array of matching foreign Record objects.
     *
     */
    protected function getMatches(RecordInterface $nativeRecord, array $foreignRecords) : array
    {
        $matches = [];

        // loop through the foreigns and append to matches in the order they are
        // already in; this honors the many-to-many "ORDER" clause, if present.
        foreach ($foreignRecords as $foreignRecord) {
            foreach ($nativeRecord->{$this->throughName} as $throughRecord) {
                if ($this->recordsMatch($throughRecord, $foreignRecord)) {
                    $matches[] = $foreignRecord;
                }
            }
        }
        return $matches;
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
        $this->persistForeignRecordSet($nativeRecord, $tracker);
    }
}
