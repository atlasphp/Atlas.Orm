<?php
namespace Atlas\Orm\Mapper;

use Atlas\Orm\Exception;

class Related
{
    private $fields = [];

    public function __construct(array $fields = [])
    {
        $this->fields = $fields;
    }

    public function __get($name)
    {
        $this->assertHas($name);
        return $this->fields[$name];
    }

    public function __set($name, $fields)
    {
        $this->assertHas($name);
        $this->fields[$name] = $fields;
    }

    public function __isset($name)
    {
        $this->assertHas($name);
        return isset($this->fields[$name]);
    }

    public function __unset($name)
    {
        $this->assertHas($name);
        $this->fields[$name] = null;
    }

    public function set($colsVals, $val = NULL)
    {
        if (is_string($colsVals))
        {
            $this->colsVals = $val;
        }
        elseif (is_array($colsVals))
        {
            $colsVals = array_intersect_key($colsVals, $this->fields);

            foreach($colsVals as $col => $val)
            {
                $this->$col = $val;
            }
        }
        else
        {
            //throw new Exception("Invalid set parameters: ".__METHOD__);
        }
    }

    protected function assertHas($name)
    {
        if (! $this->has($name)) {
            throw Exception::propertyDoesNotExist($this, $name);
        }
    }

    public function has($name)
    {
        return array_key_exists($name, $this->fields);
    }

    public function getArrayCopy()
    {
        $array = [];
        foreach ($this->fields as $name => $foreign) {
            $array[$name] = $foreign;
            if ($foreign) {
                $array[$name] = $foreign->getArrayCopy();
            }
        }
        return $array;
    }
}
