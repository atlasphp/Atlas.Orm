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
    public function getSettings();

    /**
     *
     * Sets the native => foreign relationship column names.
     *
     * @param array
     *
     */
    public function on(array $on);

    /**
     *
     * Returns the native => foreign column names.
     *
     * @return array
     *
     */
    public function getOn();

    /**
     *
     * Returns the foreign Mapper instance.
     *
     * @return MapperInterface
     *
     */
    public function getForeignMapper();

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
    );

    public function fixNativeRecordKeys(RecordInterface $record);

    public function fixForeignRecordKeys(RecordInterface $record);

    public function persist(RecordInterface $record);
}
