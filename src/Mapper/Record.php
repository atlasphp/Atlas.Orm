<?php
namespace Atlas\Orm\Mapper;

use Atlas\Orm\Exception;
use Atlas\Orm\Table\RowInterface;

class Record implements RecordInterface
{
    private $row;
    private $related;
    private $mapperClass;

    public function __construct($mapperClass, RowInterface $row, Related $related)
    {
        $this->mapperClass = $mapperClass;
        $this->row = $row;
        $this->related = $related;
    }

    public function __get($field)
    {
        $prop = $this->assertHas($field);
        return $this->$prop->$field;
    }

    public function __set($field, $value)
    {
        $prop = $this->assertHas($field);
        $this->$prop->$field = $value;
    }

    public function __isset($field)
    {
        $prop = $this->assertHas($field);
        return isset($this->$prop->$field);
    }

    public function __unset($field)
    {
        $prop = $this->assertHas($field);
        unset($this->$prop->$field);
    }

    public function set(array $colsVals = [])
    {
        $this->row->set($colsVals);
        //$this->related->set($colsVals);
    }

    public function getMapperClass()
    {
        return $this->mapperClass;
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
        return $this->row->getArrayCopy()
             + $this->related->getArrayCopy();
    }

    protected function assertHas($field)
    {
        if ($this->row->has($field)) {
            return 'row';
        }

        if ($this->related->has($field)) {
            return 'related';
        }

        throw Exception::propertyDoesNotExist($this, $field);
    }
}
