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
     * Does the table have a particular column?
     *
     * @return bool
     *
     */
    public function hasCol($name)
    {
        return in_array($name, $this->getCols());
    }
}
