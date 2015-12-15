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

    private $tableClass;

    public function __construct($tableClass, array $rows = [])
    {
        $this->tableClass = $tableClass;
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
            throw Exception::invalidType('Atlas\Orm\Table\Row', gettype($value));
        }

        if (! $value instanceof Row) {
            throw Exception::invalidType('Atlas\Orm\Table\Row', get_class($value));
        }

        $value->assertTableClass($this->tableClass);

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
