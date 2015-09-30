<?php
namespace Atlas\Mapper;

use Atlas\Exception;
use Atlas\Table\Row;

/**
 *
 * This is a "passive" record, not an active one. It is primarily for mapping a
 * row *and its related rows* regarding persistence.
 *
 */
class Record
{
    protected $row;
    protected $related;

    public function __construct(Row $row, Related $related)
    {
        $this->row = $row;
        $this->related = $related;
    }

    public function __get($field)
    {
        if (isset($this->row->$field)) {
            return $this->row->$field;
        }

        if (! isset($this->related->$field)) {
            $class = get_class($this);
            throw new Exception("{$class}->{$field} does not exist");
        }

        return $this->related->$field;
    }

    public function __set($field, $value)
    {
        if (isset($this->row->$field)) {
            $this->row->$field = $value;
            return;
        }

        if (! isset($this->related->$field)) {
            $class = get_class($this);
            throw new Exception("{$class}->{$field} does not exist");
        }

        $this->related->$field = $value;
    }

    public function __isset($field)
    {
        return isset($this->row->$field)
            || isset($this->related->$field);
    }

    public function __unset($field)
    {
        if (isset($this->row->$field)) {
            unset($this->row->$field);
            return;
        }

        if (! isset($this->related->$field)) {
            $class = get_class($this);
            throw new Exception("{$class}->{$field} does not exist");
        }

        unset($this->related->$field);
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
