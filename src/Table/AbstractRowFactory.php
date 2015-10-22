<?php
namespace Atlas\Table;

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

    abstract public function getRowClass();

    abstract public function getRowIdentityClass();

    abstract public function getRowSetClass();
}
