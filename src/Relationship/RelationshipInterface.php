<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Orm\Relationship;

use Atlas\Orm\Mapper\MapperInterface;
use Atlas\Orm\Mapper\RecordInterface;
use SplObjectStorage;

/**
 *
 * Interface for a relationship between Mapper objects.
 *
 * @package atlas/orm
 *
 */
interface RelationshipInterface
{
    /**
     *
     * Returns the settings properties for this relationship.
     *
     * @return array
     *
     */
    public function getSettings() : array;

    /**
     *
     * Sets the native => foreign relationship column names.
     *
     * @param array $on The native => foreign column names.
     *
     */
    public function on(array $on) : RelationshipInterface;

    /**
     *
     * Adds a WHERE condition, with optional bind values, to the relationship.
     *
     * @param string $cond The WHERE condition.
     *
     * @param array ...$bind Values to bind into the WHERE condition.
     *
     */
    public function where(string $cond, ...$bind) : RelationshipInterface;

    /**
     *
     * Returns the native => foreign column names.
     *
     * @return array
     *
     */
    public function getOn() : array;

    /**
     *
     * Returns the foreign Mapper instance.
     *
     * @return MapperInterface
     *
     */
    public function getForeignMapper() : MapperInterface;

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
    ) : void;

    /**
     *
     * Given a native Record, sets the related foreign Record values into the
     * native Record.
     *
     * @param RecordInterface $nativeRecord The native Record to work with.
     *
     */
    public function fixNativeRecordKeys(RecordInterface $nativeRecord) : void;

    /**
     *
     * Given a native Record, sets the appropriate native Record values into all
     * related foreign Records.
     *
     * @param RecordInterface $nativeRecord The native Record to work with.
     *
     */
    public function fixForeignRecordKeys(RecordInterface $nativeRecord) : void;

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
    public function persistForeign(RecordInterface $nativeRecord, SplObjectStorage $tracker) : void;
}
