<?php
namespace Atlas\Table;

use InvalidArgumentException;

abstract class AbstractRowFactory
{
    public function newRow(array $cols)
    {
        $cols = array_merge($this->getDefault(), $cols);
        $rowIdentity = $this->newRowIdentity($cols);
        $rowClass = $this->getRowClass();
        return new $rowClass($rowIdentity, $cols);
    }

    protected function newRowIdentity(array &$cols)
    {
        $primaryCol = $this->getPrimary();
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
        $rowSetClass = $this->getRowSetClass();
        return new $rowSetClass($rows, $this->getRowClass());
    }

    public function assertRowClass(AbstractRow $row)
    {
        $rowClass = $this->getRowClass();
        if (! $row instanceof $rowClass) {
            $actual = get_class($row);
            throw new InvalidArgumentException("Expected {$rowClass}, got {$actual} instead");
        }
    }

    /**
     *
     * Default values for a new row.
     *
     * @return array
     *
     */
    abstract public function getDefault();

    /**
     *
     * Returns the primary column name on the table.
     *
     * @return string The primary column name.
     *
     */
    abstract public function getPrimary();

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
