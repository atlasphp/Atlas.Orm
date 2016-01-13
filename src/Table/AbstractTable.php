<?php
namespace Atlas\Orm\Table;

use Atlas\Orm\Mapper\Select;

abstract class AbstractTable implements TableInterface
{
    private $primaryKey;

    public function __construct()
    {
        $this->primaryKey = $this->getPrimaryKey();
        if (count($this->primaryKey) == 1) {
            $this->primaryKey = current($this->primaryKey);
        }
    }

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
     * Returns the primary key column names on the table.
     *
     * @return array The primary key column names.
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
     * Returns the Row class for this Table.
     *
     * @return string
     *
     */
    public function getRowClass()
    {
        static $rowClass;
        if (! $rowClass) {
            $rowClass = substr(static::CLASS, 0, -5) . 'Row';
            $rowClass = class_exists($rowClass)
                ? $rowClass
                : Row::CLASS;
        }
        return $rowClass;
    }

    public function calcPrimary($primaryVal)
    {
        if (is_array($this->primaryKey)) {
            return $this->calcPrimaryComposite($primaryVal);
        }

        if (is_array($primaryVal) && isset($primaryVal[$this->primaryKey])) {
            $primaryVal = $primaryVal[$this->primaryKey];
        }

        if (! is_scalar($primaryVal)) {
            throw new Exception("Primary key value must be scalar");
        }

        return [$this->primaryKey => $primaryVal];
    }

    private function calcPrimaryComposite(array $primaryVal)
    {
        $primary = [];
        foreach ($this->primaryKey as $primaryCol) {
            $primary[$primaryCol] = null;
            if (! isset($primaryVal[$primaryCol])) {
                continue;
            }
            if (! is_scalar($primaryVal[$primaryCol])) {
                throw new Exception("Primary key value for '$primaryCol' must be scalar");
            }
            $primary = $primaryVal[$primaryCol];
        }
        return $primary;
    }
}
