<?php
namespace Atlas\Orm\Table;

use Atlas\Orm\Exception;
use Atlas\Orm\Status;

class Row
{
    private $tableClass;

    private $identity;

    private $data = [];

    private $status;

    public function __construct($tableClass, RowIdentity $identity, array $data)
    {
        $this->tableClass = $tableClass;
        $this->identity = $identity;
        $this->data = $data;
        $this->status = Status::IS_NEW;
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

        $this->modify($col, $val);
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

        $this->modify($col, null);
    }

    protected function assertHas($col)
    {
        if (! $this->has($col)) {
            throw Exception::propertyDoesNotExist($this, $col);
        }
    }

    public function getTableClass()
    {
        return $this->tableClass;
    }

    public function assertTableClass($tableClass)
    {
        if ($tableClass !== $this->tableClass) {
            throw Exception::wrongTableClass($tableClass, $this->tableClass);
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

    /** @todo array_key_exists($col, $init) */
    public function getArrayDiff(array $init)
    {
        $diff = $this->getArrayCopy();
        foreach ($diff as $col => $val) {
            if ($this->isSameValue($init[$col], $diff[$col])) {
                unset($diff[$col]);
            }
        }
        return $diff;
    }

    public function getIdentity()
    {
        return $this->identity;
    }

    protected function modify($col, $new)
    {
        if ($this->isDeleted()) {
            throw Exception::immutableOnceDeleted($this, $col);
        }

        if ($this->isNew() || $this->isTrash()) {
            $this->data[$col] = $new;
            return;
        }

        $old = $this->data[$col];
        $this->data[$col] = $new;
        if (! $this->isSameValue($old, $new)) {
            $this->status = Status::IS_DIRTY;
        }
    }

    protected function isSameValue($old, $new)
    {
        return (is_numeric($old) && is_numeric($new))
            ? $old == $new // numeric, compare loosely
            : $old === $new; // not numeric, compare strictly
    }

    public function isNew()
    {
        return $this->status == Status::IS_NEW;
    }

    public function isClean()
    {
        return $this->status == Status::IS_CLEAN;
    }

    public function isDirty()
    {
        return $this->status == Status::IS_DIRTY;
    }

    public function isSaved()
    {
        return $this->status == Status::IS_SAVED;
    }

    public function isTrash()
    {
        return $this->status == Status::IS_TRASH;
    }

    public function isDeleted()
    {
        return $this->status == Status::IS_DELETED;
    }

    public function markAsClean()
    {
        $this->status = Status::IS_CLEAN;
    }

    public function markAsSaved()
    {
        $this->status = Status::IS_SAVED;
    }

    public function markAsTrash()
    {
        $this->status = Status::IS_TRASH; // if not deleted, and not new
    }

    public function markAsDeleted()
    {
        $this->status = Status::IS_DELETED;
    }

    public function getStatus()
    {
        return $this->status;
    }
}
