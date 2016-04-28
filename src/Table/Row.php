<?php
namespace Atlas\Orm\Table;

use Atlas\Orm\Exception;

class Row implements RowInterface
{
    // new instance, in memory only
    const FOR_INSERT = 'FOR_INSERT';

    // selected, and not yet modified in memory
    const SELECTED = 'SELECTED';

    // selected/inserted/updated, then modified in memory
    const MODIFIED = 'MODIFIED';

    // marked for deletion but not deleted, modification in memory allowed
    const FOR_DELETE = 'FOR_DELETE';

    // inserted, and not again modified in memory
    const INSERTED = 'INSERTED';

    // updated, and not again modified in memory
    const UPDATED = 'UPDATED';

    // deleted, modification in memory not allowed
    const DELETED = 'DELETED';

    private $primary;

    private $cols = [];

    private $status;

    public function __construct(array $cols)
    {
        $this->cols = $cols;
        $this->status = static::FOR_INSERT;
    }

    public function __get($col)
    {
        $this->assertHas($col);
        return $this->cols[$col];
    }

    public function __set($col, $val)
    {
        $this->assertHas($col);
        $this->modify($col, $val);
    }

    public function __isset($col)
    {
        $this->assertHas($col);
        return isset($this->cols[$col]);
    }

    public function __unset($col)
    {
        $this->assertHas($col);
        $this->modify($col, null);
    }

    public function set($colsVals, $val = NULL)
    {
        if (is_string($colsVals))
        {
            $this->colsVals = $val;
        }
        elseif (is_array($colsVals))
        {
            $colsVals = array_intersect_key($colsVals, $this->cols);

            foreach($colsVals as $col => $val)
            {
                $this->$col = $val;
            }
        }
        else
        {
            //throw new Exception("Invalid set parameters: ".__METHOD__);
        }
    }

    protected function assertHas($col)
    {
        if (! $this->has($col)) {
            throw Exception::propertyDoesNotExist($this, $col);
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

    protected function modify($col, $new)
    {
        if ($this->status == static::DELETED) {
            throw Exception::immutableOnceDeleted($this, $col);
        }

        if ($this->status == static::FOR_INSERT) {
            $this->cols[$col] = $new;
            return;
        }

        $old = $this->cols[$col];
        $this->cols[$col] = $new;
        if (! $this->isSameValue($old, $new)) {
            $this->setStatus(static::MODIFIED);
        }
    }

    protected function isSameValue($old, $new)
    {
        return (is_numeric($old) && is_numeric($new))
            ? $old == $new // numeric, compare loosely
            : $old === $new; // not numeric, compare strictly
    }

    public function hasStatus($status)
    {
        return in_array($this->status, (array) $status);
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $const = get_class($this) . '::' . $status;
        if (! defined($const)) {
            throw Exception::invalidStatus($status);
        }
        $this->status = $status;
    }
}
