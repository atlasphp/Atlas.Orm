<?php
namespace Atlas\Orm\Table;

use ArrayAccess;
use ArrayIterator;
use Atlas\Orm\Exception;
use Countable;
use IteratorAggregate;

class RowSet implements ArrayAccess, Countable, IteratorAggregate
{
    private $rowFactory;

    private $rows = [];

    public function __construct(RowFactory $rowFactory, array $rows = [])
    {
        $this->rowFactory = $rowFactory;
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
        $this->rowFactory->assertRowClass($value);

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
