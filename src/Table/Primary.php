<?php
namespace Atlas\Orm\Table;

use Atlas\Orm\Exception;

// using arrays to plan ahead for composite key
class Primary
{
    private $key;

    public function __construct(array $key)
    {
        $this->key = $key;
    }

    public function __get($col)
    {
        $this->assertHas($col);
        return $this->key[$col];
    }

    public function __set($col, $val)
    {
        $this->assertHas($col);

        if (isset($this->key[$col])) {
            throw Exception::immutableOnceSet($this, $col);
        }

        $this->key[$col] = $val;
    }

    public function __isset($col)
    {
        $this->assertHas($col);
        return isset($this->key[$col]);
    }

    public function __unset($col)
    {
        $this->assertHas($col);

        if (isset($this->key[$col])) {
            throw Exception::immutableOnceSet($this, $col);
        }

        $this->key[$col] = null;
    }

    protected function assertHas($col)
    {
        if (! $this->has($col)) {
            throw Exception::immutableOnceSet($this, $col);
        }
    }

    public function has($col)
    {
        return array_key_exists($col, $this->key);
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getVal()
    {
        return current($this->key);
    }
}
