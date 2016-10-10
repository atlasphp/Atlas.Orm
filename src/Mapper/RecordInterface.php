<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Orm\Mapper;

/**
 *
 * An interface for Record objects.
 *
 * @package atlas/orm
 *
 */
interface RecordInterface
{
    /**
     *
     * Returns the Mapper class for this Record.
     *
     * @return string
     *
     */
    public function getMapperClass();

    /**
     *
     * Gets the native Row for this Record.
     *
     * @return RowInterface
     *
     */
    public function getRow();

    /**
     *
     * Gets the related foreign Record and RecordSet objects for this Record.
     *
     * @return Related
     *
     */
    public function getRelated();

    /**
     *
     * Sets many field values at one time.
     *
     * @param array $fieldsValues An array of key-value pairs where the key is
     * the field name and the value is the value to set.
     *
     */
    public function set(array $fieldsValues);

    /**
     *
     * Does the Record have a particular field?
     *
     * @return bool
     *
     */
    public function has($field);

    /**
     *
     * Returns an array of the Row and Related fields for this Record.
     *
     * @return array
     *
     */
    public function getArrayCopy();
}
