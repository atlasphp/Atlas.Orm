<?php
namespace Atlas\Orm\Table;

abstract class AbstractTable implements TableInterface
{
    /**
     *
     * Returns the table name.
     *
     * @return string
     *
     */
    abstract public function getName();

    /**
     *
     * Returns the table column names.
     *
     * @return array
     *
     */
    abstract public function getColNames();

    /**
     *
     * Returns the table column information.
     *
     * @return array
     *
     */
    abstract public function getCols();

    /**
     *
     * Returns the primary key on the table, typically a single column name.
     *
     * @return string The primary column name.
     *
     */
    abstract public function getPrimaryKey();

    /**
     *
     * Does the database set the primary key value on insert by autoincrement?
     *
     * @return bool
     *
     */
    abstract public function getAutoinc();

    /**
     *
     * Returns the default values for a new row.
     *
     * @return array
     *
     */
    abstract public function getColDefaults();

    /**
     *
     * Returns a new Row for this table.
     *
     * @return RowInterface
     *
     */
    public function newRow(array $cols = [])
    {
        $cols = array_merge($this->getColDefaults(), $cols);
        $primary = $this->newPrimary($cols);
        $rowClass = $this->getRowClass();
        $row = new $rowClass(get_class($this), $primary, $cols);
        return $row;
    }

    /**
     *
     * Returns a new Primary for a Row.
     *
     * @return Primary
     *
     */
    protected function newPrimary(array &$cols)
    {
        $primaryCol = $this->getPrimaryKey();
        $primaryVal = null;
        if (array_key_exists($primaryCol, $cols)) {
            $primaryVal = $cols[$primaryCol];
            unset($cols[$primaryCol]);
        }
        return new Primary([$primaryCol => $primaryVal]);
    }

    /**
     *
     * Returns the Row class for this table.
     *
     * @return string
     *
     */
    protected function getRowClass()
    {
        static $rowClass;
        if (! $rowClass) {
            $rowClass = substr(get_class($this), 0, -5) . 'Row';
            $rowClass = class_exists($rowClass)
                ? $rowClass
                : Row::CLASS;
        }
        return $rowClass;
    }
}
