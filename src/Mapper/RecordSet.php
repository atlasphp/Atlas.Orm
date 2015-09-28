<?php
namespace Atlas\Mapper;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;

class RecordSet implements ArrayAccess, Countable, IteratorAggregate
{
    protected $records = [];

    public function __construct(array $records)
    {
        $this->records = $records;
    }

    public function offsetExists($offset)
    {
        return isset($this->records[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->records[$offset];
    }

    /**
     * @todo assert $value is a Record instance
     */
    public function offsetSet($offset, $value)
    {
        if ($offset === null) {
            $this->records[] = $value;
        } else {
            $this->records[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->records[$offset]);
    }

    public function count()
    {
        return count($this->records);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->records);
    }

    public function isEmpty()
    {
        return ! $this->records;
    }

    public function getArrayCopy()
    {
        $array = [];
        foreach ($this as $key => $record) {
            $array[$key] = $record->getArrayCopy();
        }
        return $array;
    }
}
