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

    public function appendNew(array $fields = [])
    {
        $record = call_user_func($this->newRecord, $fields);
        $this->records[] = $record;
        return $record;
    }

    public function getOneBy(array $whereEquals)
    {
        foreach ($this->records as $i => $record) {
            if ($this->compareBy($record, $whereEquals)) {
                return $record;
            }
        }
        return false;
    }

    public function getAllBy(array $whereEquals)
    {
        $found = [];
        foreach ($this->records as $i => $record) {
            if ($this->compareBy($record, $whereEquals)) {
                $found[$i] = $record;
            }
        }
        return $found;
    }

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

    public function removeAllBy(array $whereEquals)
    {
        $removed = [];
        foreach ($this->records as $i => $record) {
            if ($this->compareBy($record, $whereEquals)) {
                unset($this->records[$i]);
                $removed[$i] = $record;
            }
        }
        return $removed;
    }

    protected function compareBy(RecordInterface $record, $whereEquals)
    {
        foreach ($whereEquals as $field => $value) {
            if ($record->$field != $value) {
                return false;
            }
        }
        return true;
    }
}
