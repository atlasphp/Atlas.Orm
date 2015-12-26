<?php
namespace Atlas\Orm\Mapper;

use Atlas\Exception;

interface TableInterface
{
    /**
     *
     * Returns the table name.
     *
     * @return string
     *
     */
    public function getName();

    /**
     *
     * Returns the table column names.
     *
     * @return array
     *
     */
    public function getColNames();

    /**
     *
     * Returns the table column information.
     *
     * @return array
     *
     */
    public function getCols();

    /**
     *
     * Returns the primary key on the table, typically a single column name.
     *
     * @return string The primary column name.
     *
     */
    public function getPrimaryKey();

    /**
     *
     * Does the database set the primary key value on insert by autoincrement?
     *
     * @return bool
     *
     */
    public function getAutoinc();

    /**
     *
     * Returns the default values for a new row.
     *
     * @return array
     *
     */
    public function getColDefaults();

    /**
     *
     * Does the table have a particular column?
     *
     * @return bool
     *
     */
    public function hasCol($name);
}
