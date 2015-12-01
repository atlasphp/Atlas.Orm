<?php
namespace Atlas\Orm\Table;

trait TableTrait
{
    /**
     *
     * Returns the table name.
     *
     * @return string
     *
     */
    public function tableName()
    {
    }

    /**
     *
     * Returns the table column names.
     *
     * @return array
     *
     */
    public function tableCols()
    {
    }

    /**
     *
     * Returns the table column information.
     *
     * @return array
     *
     */
    public function tableInfo()
    {
    }

    /**
     *
     * Returns the primary column name on the table.
     *
     * @return string The primary column name.
     *
     */
    public function tablePrimary()
    {
    }

    /**
     *
     * Does the database set the primary key value on insert by autoincrement?
     *
     * @return bool
     *
     */
    public function tableAutoinc()
    {
    }

    /**
     *
     * Returns the default values for a new row.
     *
     * @return array
     *
     */
    public function tableDefault()
    {
    }
}
