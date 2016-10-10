<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Orm\Table;

use Atlas\Exception;

/**
 *
 * __________
 *
 * @package atlas/orm
 *
 */
interface TableInterface
{
    /**
     *
     * Returns the database read connection.
     *
     * @return ExtendedPdoInterface
     *
     */
    public function getReadConnection();

    /**
     *
     * Returns the database write connection.
     *
     * @return ExtendedPdoInterface
     *
     */
    public function getWriteConnection();

    public function fetchRow($primaryVal);

    public function fetchRows(array $primaryVals);

    public function select(array $colsVals = []);

    public function selectRow(TableSelect $select);

    public function selectRows(TableSelect $select);

    public function insert(RowInterface $row);

    public function update(RowInterface $row);

    public function delete(RowInterface $row);

    /**
     *
     * Returns a new Row for the table.
     *
     * @return RowInterface
     *
     */
    public function newRow(array $cols = []);

    public function newSelectedRow(array $cols);

    public function getSelectedRow(array $cols);

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
