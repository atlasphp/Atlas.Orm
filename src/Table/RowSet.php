<?php
namespace Atlas\Orm\Table;

use ArrayAccess;
use ArrayIterator;
use Atlas\Orm\Exception;
use Countable;
use IteratorAggregate;

class RowSet implements ArrayAccess, Countable, IteratorAggregate
{
    private $rows = [];

    private $rowClass;

    public function __construct(array $rows = [])
    {
        $this->rowClass = substr(get_class($this), 0, -3);
        foreach ($rows as $key => $row) {
            $this->offsetSet($key, $row);
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->rows[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->rows[$offset];
    }

    public function offsetSet($offset, $value)
    {
        if (! is_object($value)) {
            throw Exception::invalidType($this->rowClass, gettype($value));
        }

        if (! $value instanceof $this->rowClass) {
            throw Exception::invalidType($this->rowClass, $value);
        }

        if ($offset === null) {
            $this->rows[] = $value;
            return;
        }

        $this->rows[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->rows[$offset]);
    }

    public function count()
    {
        return count($this->rows);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->rows);
    }

    public function getArrayCopy()
    {
        $array = [];
        foreach ($this->rows as $key => $row) {
            $array[$key] = $row->getArrayCopy();
        }
        return $array;
    }
}
