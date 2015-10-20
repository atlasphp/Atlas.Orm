<?php
namespace Atlas\Mapper;

use Atlas\Exception;
use Atlas\Table\AbstractRow;

/**
 *
 * This is a "passive" record, not an active one. It is primarily for mapping a
 * row *and its related rows* regarding persistence.
 *
 */
class Record
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
        throw new Exception("{$class}::\${$field} does not exist");
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
        throw new Exception("{$class}::\${$field} does not exist");
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
        throw new Exception("{$class}::\${$field} does not exist");
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
        throw new Exception("{$class}::\${$field} does not exist");
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
