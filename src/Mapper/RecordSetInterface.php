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
use JsonSerializable;

/**
 *
 * An interface for RecordSet objects.
 *
 * @package atlas/orm
 *
 */
interface RecordSetInterface extends ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    /**
     *
     * Is the RecordSet empty?
     *
     * @return bool
     *
     */
    public function isEmpty() : bool;

    /**
     *
     * Returns an array copy of the Record objects in the RecordSet.
     *
     * @return array
     *
     */
    public function getArrayCopy() : array;

    /**
     *
     * Appends a new Record to the RecordSet.
     *
     * @param array $fields Field values for the new Record.
     *
     * @return RecordInterface The appended Record.
     *
     */
    public function appendNew(array $fields = []) : RecordInterface;

    /**
     *
     * Returns one Record matching an array of column-value equality pairs.
     *
     * @param array $whereEquals The column-value equality pairs.
     *
     * @return RecordInterface|false A Record on success, or false on failure.
     *
     */
    public function getOneBy(array $whereEquals) : ?RecordInterface;

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
    public function getAllBy(array $whereEquals) : array;

    /**
     *
     * Removes one Record matching an array of column-value equality pairs.
     *
     * @param array $whereEquals The column-value equality pairs.
     *
     * @return RecordInterface|false The removed Record, or false if none matched.
     *
     */
    public function removeOneBy(array $whereEquals) : ?RecordInterface;

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
    public function removeAllBy(array $whereEquals) : array;
}
