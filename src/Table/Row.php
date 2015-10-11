<?php
namespace Atlas\Table;

use Atlas\Exception;

/**
 * @todo Should $primary really be a constructor param? Or should it figure it
 * out the same way the table does? Or should it be an Identity object?
 */
class Row
{
    protected $identity;

    // should default data be on the table, not the row?
    protected $data = []; // current data, including default values

    public function __construct(RowIdentity $identity, array $data)
    {
        $this->identity = $identity;
        $this->data = array_merge($this->data, $data);
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
            $class = get_class($this);
            throw new Exception("{$class}::\${$col} does not exist");
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

    public function getObjectCopy()
    {
        return (object) $this->getArrayCopy();
    }

    public function getIdentity()
    {
        return $this->identity;
    }
}
