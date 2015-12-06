<?php
namespace Atlas\Orm\Table;

use Atlas\Orm\Exception;

class Row
{
    // new row instance (not in the database yet)
    const IS_NEW = 'IS_NEW';

    // selected and unmodified
    const IS_CLEAN = 'IS_CLEAN';

    // selected/inserted/updated, then changed
    const IS_DIRTY = 'IS_DIRTY';

    // inserted/updated, and unchanged
    const IS_SAVED = 'IS_SAVED';

    // marked for deletion but not deleted, changes are allowed but unimportant
    const IS_TRASH = 'IS_TRASH';

    // deleted, changes are not allowed
    const IS_DELETED = 'IS_DELETED';

    private $identity;

    private $data = [];

    private $status;

    public function __construct(RowIdentity $identity, array $data)
    {
        $this->identity = $identity;
        $this->data = $data;
        $this->status = static::IS_NEW;
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
            $this->status = static::IS_DIRTY;
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
        return $this->status == static::IS_NEW;
    }

    public function isClean()
    {
        return $this->status == static::IS_CLEAN;
    }

    public function isDirty()
    {
        return $this->status == static::IS_DIRTY;
    }

    public function isSaved()
    {
        return $this->status == static::IS_SAVED;
    }

    public function isTrash()
    {
        return $this->status == static::IS_TRASH;
    }

    public function isDeleted()
    {
        return $this->status == static::IS_DELETED;
    }

    public function markAsClean()
    {
        $this->status = static::IS_CLEAN;
    }

    public function markAsSaved()
    {
        $this->status = static::IS_SAVED;
    }

    public function markAsTrash()
    {
        $this->status = static::IS_TRASH;
    }

    public function markAsDeleted()
    {
        $this->status = static::IS_DELETED;
    }

    public function getStatus()
    {
        return $this->status();
    }
}
