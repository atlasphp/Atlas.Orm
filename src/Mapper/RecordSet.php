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
use ArrayIterator;
use Atlas\Orm\Exception;
use Countable;
use IteratorAggregate;

/**
 *
 * A generic RecordSet.
 *
 * @package atlas/orm
 *
 */
class RecordSet implements RecordSetInterface
{
    /**
     *
     * The Record objects in this set.
     *
     * @var array
     *
     */
    private $records = [];

    /**
     *
     * Constructor.
     *
     * @param array $records The Record objects in this set.
     *
     */
    public function __construct(
        array $records = [],
        callable $newRecord
    ) {
        $this->newRecord = $newRecord;
        foreach ($records as $key => $record) {
            $this->offsetSet($key, $record);
        }
    }

    /**
     *
     * Implements ArrayAccess::offsetExists().
     *
     * @param mixed $offset An offset to check for.
     *
     * @return bool
     *
     */
    public function offsetExists($offset)
    {
        return isset($this->records[$offset]);
    }

    /**
     *
     * Implements ArrayAccess::offsetGet().
     *
     * @param mixed $offset The offset to retrieve.
     *
     * @return RecordInterface
     *
     */
    public function offsetGet($offset)
    {
        return $this->records[$offset];
    }

    /**
     *
     * Implements ArrayAccess::offsetSet().
     *
     * @param mixed $offset The offset to assign the Record to.
     *
     * @param RecordInterface $value The Record to set.
     *
     */
    public function offsetSet($offset, $value)
    {
        if (! is_object($value)) {
            throw Exception::invalidType(RecordInterface::CLASS, gettype($value));
        }

        if (! $value instanceof RecordInterface) {
            throw Exception::invalidType(RecordInterface::CLASS, $value);
        }

        if ($offset === null) {
            $this->records[] = $value;
            return;
        }

        $this->records[$offset] = $value;
    }

    /**
     *
     * Implements ArrayAccess::offsetUnset().
     *
     * @param mixed $offset The offset to unset.
     *
     */
    public function offsetUnset($offset)
    {
        unset($this->records[$offset]);
    }

    /**
     *
     * Implements Countable::count().
     *
     * @return int The number of Record objects in the RecordSet.
     *
     */
    public function count()
    {
        return count($this->records);
    }

    /**
     *
     * Implements IteratorAggregate::getIterator().
     *
     * @return ArrayIterator
     *
     */
    public function getIterator()
    {
        return new ArrayIterator($this->records);
    }

    /**
     *
     * Is the RecordSet empty?
     *
     * @return bool
     *
     */
    public function isEmpty()
    {
        return ! $this->records;
    }

    /**
     *
     * Returns an array copy of the Record objects in the RecordSet.
     *
     * @return array
     *
     */
    public function getArrayCopy()
    {
        $array = [];
        foreach ($this as $key => $record) {
            $array[$key] = $record->getArrayCopy();
        }
        return $array;
    }

    /**
     *
     * Appends a new Record to the RecordSet.
     *
     * @param array $cols Column values for the Row in the new Record.
     *
     * @return RecordInterface The appended Record.
     *
     */
    public function appendNew(array $cols = [])
    {
        $record = call_user_func($this->newRecord, $cols);
        $this->records[] = $record;
        return $record;
    }

    /**
     *
     * Returns one Record matching an array of column-value equality pairs.
     *
     * @param array $whereEquals The column-value equality pairs.
     *
     * @return RecordInterface|false A Record on success, or false on failure.
     *
     */
    public function getOneBy(array $whereEquals)
    {
        foreach ($this->records as $i => $record) {
            if ($this->compareBy($record, $whereEquals)) {
                return $record;
            }
        }
        return false;
    }

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
    public function getAllBy(array $whereEquals)
    {
        $records = [];
        foreach ($this->records as $i => $record) {
            if ($this->compareBy($record, $whereEquals)) {
                $records[$i] = $record;
            }
        }
        return $records;
    }

    /**
     *
     * Removes one Record matching an array of column-value equality pairs.
     *
     * @param array $whereEquals The column-value equality pairs.
     *
     * @return RecordInterface|false The removed Record, or false if none matched.
     *
     */
    public function removeOneBy(array $whereEquals)
    {
        foreach ($this->records as $i => $record) {
            if ($this->compareBy($record, $whereEquals)) {
                unset($this->records[$i]);
                return $record;
            }
        }
        return false;
    }

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
    public function removeAllBy(array $whereEquals)
    {
        $records = [];
        foreach ($this->records as $i => $record) {
            if ($this->compareBy($record, $whereEquals)) {
                unset($this->records[$i]);
                $records[$i] = $record;
            }
        }
        return $records;
    }

    /**
     *
     * Compares a Record with an array of column-value equality pairs.
     *
     * @param RecordInterface The Record to examine.
     *
     * @param array $whereEquals Compare with these values.
     *
     * @return bool
     *
     */
    protected function compareBy(RecordInterface $record, array $whereEquals)
    {
        foreach ($whereEquals as $field => $value) {
            if ($record->$field != $value) {
                return false;
            }
        }
        return true;
    }
}
