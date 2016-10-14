<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Orm\Mapper;

use ArrayAccess;
use Countable;
use IteratorAggregate;

/**
 *
 * An interface for RecordSet objects.
 *
 * @package atlas/orm
 *
 */
interface RecordSetInterface extends ArrayAccess, Countable, IteratorAggregate
{
    /**
     *
     * Is the RecordSet empty?
     *
     * @return bool
     *
     */
    public function isEmpty();

    /**
     *
     * Returns an array copy of the Record objects in the RecordSet.
     *
     * @return array
     *
     */
    public function getArrayCopy();

    /**
     *
     * Appends a new Record to the RecordSet.
     *
     * @param array $cols Column values for the Row in the new Record.
     *
     * @return RecordInterface The appended Record.
     *
     */
    public function appendNew(array $cols = []);

    /**
     *
     * Returns one Record matching an array of column-value equality pairs.
     *
     * @param array $whereEquals The column-value equality pairs.
     *
     * @return RecordInterface|false A Record on success, or false on failure.
     *
     */
    public function getOneBy(array $whereEquals);

    /**
     *
     * Returns all Records matching an array of column-value equality pairs.
     *
     * @param array $whereEquals The column-value equality pairs.
     *
     * @return array An array of Record objects, with the same array keys as in
     * this RecordSet.
     *
     */
    public function getAllBy(array $whereEquals);

    /**
     *
     * Removes one Record matching an array of column-value equality pairs.
     *
     * @param array $whereEquals The column-value equality pairs.
     *
     * @return RecordInterface|false The removed Record, or false if none matched.
     *
     */
    public function removeOneBy(array $whereEquals);

    /**
     *
     * Removes all Records matching an array of column-value equality pairs.
     *
     * @param array $whereEquals The column-value equality pairs.
     *
     * @return array An array of removed Record objects, with the same array
     * keys as in this RecordSet.
     *
     */
    public function removeAllBy(array $whereEquals);
}
