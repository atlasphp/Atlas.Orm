<?php
namespace Atlas\Orm\Mapper;

use Atlas\Orm\Exception;
use Atlas\Orm\Mapper\Status;

class Row
{
    private $tableClass;

    private $primary;

    private $cols = [];

    private $status;

    public function __construct($tableClass, Primary $primary, array $cols)
    {
        $this->tableClass = $tableClass;
        $this->primary = $primary;
        $this->cols = $cols;
        $this->status = Status::IS_NEW;
    }

    public function __get($col)
    {
        $this->assertHas($col);

        if ($this->primary->has($col)) {
            return $this->primary->$col;
        }

        return $this->cols[$col];
    }

    public function __set($col, $val)
    {
        $this->assertHas($col);

        if ($this->primary->has($col)) {
            $this->primary->$col = $val;
            return;
        }

        $this->modify($col, $val);
    }

    public function __isset($col)
    {
        $this->assertHas($col);

        if ($this->primary->has($col)) {
            return isset($this->primary->$col);
        }

        return isset($this->cols[$col]);
    }

    public function __unset($col)
    {
        $this->assertHas($col);

        if ($this->primary->has($col)) {
            unset($this->primary->$col);
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
        return array_key_exists($col, $this->cols)
            || $this->primary->has($col);
    }

    public function getArrayCopy()
    {
        return array_merge(
            $this->primary->getKey(),
            $this->cols
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

    public function getPrimary()
    {
        return $this->primary;
    }

    protected function modify($col, $new)
    {
        if ($this->isDeleted()) {
            throw Exception::immutableOnceDeleted($this, $col);
        }

        if ($this->isNew() || $this->isTrash()) {
            $this->cols[$col] = $new;
            return;
        }

        $old = $this->cols[$col];
        $this->cols[$col] = $new;
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
