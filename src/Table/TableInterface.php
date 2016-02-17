<?php
namespace Atlas\Orm\Table;

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
     * Returns the primary key column names on the table.
     *
     * @return array The primary key column names.
     *
     */
    public function getPrimaryKey();

    /**
     *
     * Returns the autoincrement column name on the table, if any.
     *
     * @return string
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
}
