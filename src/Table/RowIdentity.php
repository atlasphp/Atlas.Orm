<?php
namespace Atlas\Orm\Table;

use Atlas\Orm\Exception;

// using arrays to plan ahead for composite keys
class RowIdentity
{
    private $primary;

    public function __construct(array $primary)
    {
        $this->primary = $primary;
    }

    public function __get($col)
    {
        $this->assertHas($col);
        return $this->primary[$col];
    }

    public function __set($col, $val)
    {
        $this->assertHas($col);

        if (isset($this->primary[$col])) {
            throw Exception::immutableOnceSet($this, $col);
        }

        $this->primary[$col] = $val;
    }

    public function __isset($col)
    {
        $this->assertHas($col);
        return isset($this->primary[$col]);
    }

    public function __unset($col)
    {
        $this->assertHas($col);

        if (isset($this->primary[$col])) {
            throw Exception::immutableOnceSet($this, $col);
        }

        $this->primary[$col] = null;
    }

    protected function assertHas($col)
    {
        if (! $this->has($col)) {
            throw Exception::immutableOnceSet($this, $col);
        }
    }

    public function has($col)
    {
        return array_key_exists($col, $this->primary);
    }

    public function getPrimary()
    {
        return $this->primary;
    }

    public function getVal()
    {
        return current($this->primary);
    }
}
