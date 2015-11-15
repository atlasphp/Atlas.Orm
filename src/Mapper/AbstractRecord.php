<?php
namespace Atlas\Orm\Mapper;

use Atlas\Orm\Exception;
use Atlas\Orm\Table\AbstractRow;

abstract class AbstractRecord
{
    private $row;
    private $related;

    public function __construct(AbstractRow $row, Related $related)
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

        $class = get_class($this);
        throw Exception::propertyDoesNotExist($class, $field);
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

        $class = get_class($this);
        throw Exception::propertyDoesNotExist($class, $field);
    }

    public function __isset($field)
    {
        if ($this->row->has($field)) {
            return isset($this->row->$field);
        }

        if ($this->related->has($field)) {
            return isset($this->related->$field);
        }

        $class = get_class($this);
        throw Exception::propertyDoesNotExist($class, $field);
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

        $class = get_class($this);
        throw Exception::propertyDoesNotExist($class, $field);
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
        return array_merge(
            $this->getRow()->getArrayCopy(),
            $this->getRelated()->getArrayCopy()
        );
    }
}
