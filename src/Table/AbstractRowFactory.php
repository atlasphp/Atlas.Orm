<?php
namespace Atlas\Orm\Table;

use Atlas\Orm\Exception;

abstract class AbstractRowFactory
{
    use AbstractTableTrait;

    public function newRow(array $cols)
    {
        $cols = array_merge($this->getDefault(), $cols);
        $rowIdentity = $this->newRowIdentity($cols);
        $rowClass = $this->getRowClass();
        return new $rowClass($rowIdentity, $cols);
    }

    protected function newRowIdentity(array &$cols)
    {
        $primaryCol = $this->tablePrimary();
        $primaryVal = null;
        if (array_key_exists($primaryCol, $cols)) {
            $primaryVal = $cols[$primaryCol];
            unset($cols[$primaryCol]);
        }

        $rowIdentityClass = $this->getRowIdentityClass();
        return new $rowIdentityClass([$primaryCol => $primaryVal]);
    }

    public function newRowSet(array $rows)
    {
        static $rowSetClass;
        if (! $rowSetClass) {
            $rowSetClass = $this->getRowSetClass();
        }

        return new $rowSetClass($this, $rows);
    }

    public function assertRowClass($row)
    {
        static $rowClass;
        if (! $rowClass) {
            $rowClass = $this->getRowClass();
        }

        if (! is_object($row)) {
            throw Exception::invalidType($rowClass, gettype($row));
        }

        if (! $row instanceof $rowClass) {
            throw Exception::invalidType($rowClass, $row);
        }
    }

    /**
     *
     * Default values for a new row.
     *
     * @return array
     *
     */
    public function getDefault()
    {
        return $this->tableDefault();
    }

    public function getRowClass()
    {
        static $rowClass;
        if (! $rowClass) {
            $rowClass = substr(get_class($this), 0, -7);
        }
        return $rowClass;
    }

    public function getRowIdentityClass()
    {
        static $rowIdentityClass;
        if (! $rowIdentityClass) {
            $rowIdentityClass = $this->getRowClass() . 'Identity';
        }
        return $rowIdentityClass;
    }

    public function getRowSetClass()
    {
        static $rowSetClass;
        if (! $rowSetClass) {
            $rowSetClass = $this->getRowClass() . 'Set';
        }
        return $rowSetClass;
    }
}
