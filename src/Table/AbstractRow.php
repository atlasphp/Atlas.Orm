<?php
namespace Atlas\Orm\Table;

use Atlas\Orm\Exception;

abstract class AbstractRow
{
    private $identity;

    private $data = [];

    public function __construct(AbstractRowIdentity $identity, array $data)
    {
        $this->identity = $identity;
        $this->data = $data;
    }

    public function __get($col)
    {
        $this->assertHas($col);

        if ($this->identity->has($col)) {
            return $this->identity->$col;
        }

        return $this->data[$col];
    }

    public function __set($col, $val)
    {
        $this->assertHas($col);

        if ($this->identity->has($col)) {
            $this->identity->$col = $val;
            return;
        }

        $this->data[$col] = $val;
    }

    public function __isset($col)
    {
        $this->assertHas($col);

        if ($this->identity->has($col)) {
            return isset($this->identity->$col);
        }

        return isset($this->data[$col]);
    }

    public function __unset($col)
    {
        $this->assertHas($col);

        if ($this->identity->has($col)) {
            unset($this->identity->$col);
            return;
        }

        $this->data[$col] = null;
    }

    protected function assertHas($col)
    {
        if (! $this->has($col)) {
            throw Exception::propertyDoesNotExist($this, $col);
        }
    }

    public function has($col)
    {
        return array_key_exists($col, $this->data)
            || $this->identity->has($col);
    }

    public function getArrayCopy()
    {
        return array_merge(
            $this->identity->getPrimary(),
            $this->data
        );
    }

    public function getArrayDiff(array $init)
    {
        $diff = $this->getArrayCopy();
        foreach ($diff as $col => $val) {
            $same = (is_numeric($diff[$col]) && is_numeric($init[$col]))
                 ? $diff[$col] == $init[$col] // numeric, compare loosely
                 : $diff[$col] === $init[$col]; // not numeric, compare strictly
            if ($same) {
                unset($diff[$col]);
            }
        }
        return $diff;
    }

    public function getIdentity()
    {
        return $this->identity;
    }
}
