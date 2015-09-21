<?php
namespace Atlas\Table;

use Atlas\Exception;

/**
 * @todo Should $primary really be a constructor param? Or should it figure it
 * out the same way the table does? Or should it be an Identity object?
 */
class Row
{
    protected $init = []; // initial data
    protected $cols = []; // current data, including default values
    protected $primaryCol; // primary column

    public function __construct(array $cols, $primaryCol)
    {
        $this->cols = array_merge($this->cols, $cols);
        $this->primaryCol = $primaryCol;

        if (! array_key_exists($this->primaryCol, $this->cols)) {
            $this->cols[$this->primaryCol] = null;
        }

        $this->init();
    }

    public function __get($col)
    {
        if (! array_key_exists($col, $this->cols)) {
            $class = get_class($this);
            throw new Exception("{$class}->{$col} does not exist");
        }

        return $this->cols[$col];
    }

    public function __set($col, $val)
    {
        if (! array_key_exists($col, $this->cols)) {
            $class = get_class($this);
            throw new Exception("{$class}->{$col} does not exist");
        }

        $setPrimary = $col == $this->primaryCol
                   && $this->cols[$this->primaryCol] !== null;
        if ($setPrimary) {
            $class = get_class($this);
            throw new Exception("{$class}->{$col} is immutable");
        }

        $this->cols[$col] = $val;
    }

    public function __isset($col)
    {
        return array_key_exists($col, $this->cols);
    }

    public function __unset($col)
    {
        if (! array_key_exists($col, $this->cols)) {
            $class = get_class($this);
            throw new Exception("{$class}->{$col} does not exist");
        }

        $this->cols[$col] = null;
    }

    public function init()
    {
        $this->init = $this->cols;
    }

    public function getPrimaryCol()
    {
        return $this->primaryCol;
    }

    public function getPrimaryVal()
    {
        return $this->cols[$this->primaryCol];
    }

    public function getArrayCopy()
    {
        return $this->cols;
    }

    public function getArrayCopyForInsert()
    {
        return $this->getArrayCopy();
    }

    public function getArrayCopyForUpdate()
    {
        $copy = $this->getArrayCopy();
        foreach ($this->cols as $col => $curr) {
            $init = $this->init[$col];
            $same = (is_numeric($curr) && is_numeric($init))
                 ? $curr == $init // numeric, compare loosely
                 : $curr === $init; // not numeric, compare strictly
            if ($same) {
                unset($copy[$col]);
            }
        }
        return $copy;
    }

    public function getObjectCopy()
    {
        return (object) $this->getArrayCopy();
    }
}
