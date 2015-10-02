<?php
namespace Atlas\Mapper;

class Related
{
    protected $foreign = [];

    public function __construct(array $foreign = [])
    {
        $this->foreign = $foreign;
    }

    public function __get($name)
    {
        return $this->foreign[$name];
    }

    public function __set($name, $foreign)
    {
        $this->foreign[$name] = $foreign;
    }

    public function __isset($name)
    {
        return array_key_exists($name, $this->foreign);
    }

    public function __unset($name)
    {
        $this->foreign[$name] = null;
    }

    public function getArrayCopy()
    {
        $array = [];
        foreach ($this->foreign as $name => $foreign) {
            if ($foreign === null) {
                continue;
            }
            $array[$name] = $foreign;
            if ($foreign) {
                $array[$name] = $foreign->getArrayCopy();
            }
        }
        return $array;
    }
}
