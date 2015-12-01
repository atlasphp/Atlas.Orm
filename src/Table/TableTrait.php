<?php
namespace Atlas\Orm\Table;

use Atlas\Exception;

trait TableTrait
{
    /**
     *
     * Returns the table name.
     *
     * @return string
     *
     */
    protected function tableName()
    {
        throw Exception::usingDefaultTableTrait(get_called_class());
    }

    /**
     *
     * Returns the table column names.
     *
     * @return array
     *
     */
    protected function tableCols()
    {
        throw Exception::usingDefaultTableTrait(get_called_class());
    }

    /**
     *
     * Returns the table column information.
     *
     * @return array
     *
     */
    protected function tableInfo()
    {
        throw Exception::usingDefaultTableTrait(get_called_class());
    }

    /**
     *
     * Returns the primary column name on the table.
     *
     * @return string The primary column name.
     *
     */
    protected function tablePrimary()
    {
        throw Exception::usingDefaultTableTrait(get_called_class());
    }

    /**
     *
     * Does the database set the primary key value on insert by autoincrement?
     *
     * @return bool
     *
     */
    protected function tableAutoinc()
    {
        throw Exception::usingDefaultTableTrait(get_called_class());
    }

    /**
     *
     * Returns the default values for a new row.
     *
     * @return array
     *
     */
    protected function tableDefault()
    {
        throw Exception::usingDefaultTableTrait(get_called_class());
    }
}
