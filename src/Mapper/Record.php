<?php
namespace Atlas\Orm\Mapper;

use Atlas\Orm\Exception;
use Atlas\Orm\Table\Row;

class Record
{
    private $row;
    private $related;

    public function __construct(Row $row, Related $related)
    {
        $this->row = $row;
        $this->related = $related;
    }

    public function __get($field)
    {
        if ($this->row->has($field)) {
            return $this->row->$field;
        }

        if ($this->related->has($field)) {
            return $this->related->$field;
        }

        throw Exception::propertyDoesNotExist($this, $field);
    }

    public function __set($field, $value)
    {
        if ($this->row->has($field)) {
            $this->row->$field = $value;
            return;
        }

        if ($this->related->has($field)) {
            $this->related->$field = $value;
            return;
        }

        throw Exception::propertyDoesNotExist($this, $field);
    }

    public function __isset($field)
    {
        if ($this->row->has($field)) {
            return isset($this->row->$field);
        }

        if ($this->related->has($field)) {
            return isset($this->related->$field);
        }

        throw Exception::propertyDoesNotExist($this, $field);
    }

    public function __unset($field)
    {
        if ($this->row->has($field)) {
            unset($this->row->$field);
            return;
        }

        if ($this->related->has($field)) {
            unset($this->related->$field);
            return;
        }

        throw Exception::propertyDoesNotExist($this, $field);
    }

    public function has($field)
    {
        return $this->row->has($field)
            || $this->related->has($field);
    }

    public function getRow()
    {
        return $this->row;
    }

    public function getRelated()
    {
        return $this->related;
    }

    public function getArrayCopy()
    {
        // use +, not array_merge(), so row takes precedence over related
        return $this->getRow()->getArrayCopy()
             + $this->getRelated()->getArrayCopy();
    }

    public function getStatus()
    {
        return $this->getRow()->getStatus();
    }
}
