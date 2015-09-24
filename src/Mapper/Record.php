<?php
namespace Atlas\Mapper;

use Atlas\Exception;
use Atlas\Table\Row;

class Record
{
    protected $row;
    protected $related;

    public function __construct(Row $row, array $related)
    {
        $this->row = $row;
        $this->related = $related;
    }

    public function __get($field)
    {
        if (isset($this->row->$field)) {
            return $this->row->$field;
        }

        if (! array_key_exists($field, $this->related)) {
            $class = get_class($this);
            throw new Exception("{$class}->{$field} does not exist");
        }

        return $this->related[$field];
    }

    public function __set($field, $value)
    {
        if (isset($this->row->$field)) {
            $this->row->$field = $value;
            return;
        }

        if (! array_key_exists($field, $this->related)) {
            $class = get_class($this);
            throw new Exception("{$class}->{$field} does not exist");
        }

        $this->related[$field] = $value;
    }

    public function __isset($field)
    {
        return isset($this->row->$field)
            || array_key_exists($field, $this->related);
    }

    public function __unset($field)
    {
        if (isset($this->row->$field)) {
            unset($this->row->$field);
            return;
        }

        if (! array_key_exists($field, $this->related)) {
            $class = get_class($this);
            throw new Exception("{$class}->{$field} does not exist");
        }

        $this->related[$field] = null;
    }

    public function getRow()
    {
        return $this->row;
    }

    public function getRelated()
    {
        return $this->related;
    }
}
