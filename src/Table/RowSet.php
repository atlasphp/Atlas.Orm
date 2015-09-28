<?php
namespace Atlas\Table;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;

class RowSet implements ArrayAccess, Countable, IteratorAggregate
{
    protected $rows = [];

    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function offsetExists($offset)
    {
        return isset($this->rows[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->rows[$offset];
    }

    /**
     * @todo assert $value is a Row instance
     */
    public function offsetSet($offset, $value)
    {
        if ($offset === null) {
            $this->rows[] = $value;
        } else {
            $this->rows[$offset] = $value;
        }
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
