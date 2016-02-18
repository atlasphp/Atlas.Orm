<?php
namespace Atlas\Orm\Table;

use Atlas\Orm\Exception;

class Primary
{
    private $cols = [];

    public function __construct(array $cols)
    {
        $this->cols = $cols;
    }

    public function __get($col)
    {
        $this->assertHas($col);
        return $this->cols[$col];
    }

    public function __set($col, $val)
    {
        $this->assertHas($col);

        if (isset($this->cols[$col])) {
            throw Exception::immutableOnceSet($this, $col);
        }

        $this->cols[$col] = $val;
    }

    public function __isset($col)
    {
        $this->assertHas($col);
        return isset($this->cols[$col]);
    }

    public function __unset($col)
    {
        $this->assertHas($col);

        if (isset($this->cols[$col])) {
            throw Exception::immutableOnceSet($this, $col);
        }

        $this->cols[$col] = null;
    }

    protected function assertHas($col)
    {
        if (! $this->has($col)) {
            throw Exception::immutableOnceSet($this, $col);
        }
    }

    public function has($col)
    {
        return array_key_exists($col, $this->cols);
    }

    public function getArrayCopy()
    {
        return $this->cols;
    }
}
