<?php
namespace Atlas\Table;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use InvalidArgumentException;

class RowSet implements ArrayAccess, Countable, IteratorAggregate
{
    protected $rows = [];

    protected $rowClass;

    public function __construct(array $rows, $rowClass)
    {
        $this->rowClass = $rowClass;
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
        if (! $value instanceof $this->rowClass) {
            throw new InvalidArgumentException("Expected {$this->rowClass}");
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
