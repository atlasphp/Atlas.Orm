<?php
namespace Atlas\Mapper;

use Atlas\Exception;

class Related
{
    protected $foreign = [];

    public function __construct(array $foreign = [])
    {
        $this->foreign = $foreign;
    }

    public function __get($name)
    {
        $this->assertHas($name);
        return $this->foreign[$name];
    }

    public function __set($name, $foreign)
    {
        $this->assertHas($name);
        $this->foreign[$name] = $foreign;
    }

    public function __isset($name)
    {
        $this->assertHas($name);
        return isset($this->foreign[$name]);
    }

    public function __unset($name)
    {
        $this->assertHas($name);
        $this->foreign[$name] = null;
    }

    protected function assertHas($name)
    {
        if (! $this->has($name)) {
            $class = get_class($this);
            throw new Exception("{$class}::\${$name} does not exist");
        }
    }

    public function has($name)
    {
        return array_key_exists($name, $this->foreign);
    }

    public function getArrayCopy()
    {
        $array = [];
        foreach ($this->foreign as $name => $foreign) {
            $array[$name] = $foreign;
            if ($foreign) {
                $array[$name] = $foreign->getArrayCopy();
            }
        }
        return $array;
    }
}
