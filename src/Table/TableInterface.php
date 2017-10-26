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
use Aura\Sql\ExtendedPdoInterface;
use Aura\SqlQuery\Common\DeleteInterface;
use Aura\SqlQuery\Common\InsertInterface;
use Aura\SqlQuery\Common\UpdateInterface;
use PDOStatement;

/**
 *
 * Table data gateway interface.
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
    public function getReadConnection() : ExtendedPdoInterface;

    /**
     *
     * Returns the database write connection.
     *
     * @return ExtendedPdoInterface
     *
     */
    public function getWriteConnection() : ExtendedPdoInterface;

    /**
     *
     * Fetches one Row based on a primary-key value, from the identity map if
     * present, or from the database if not.
     *
     * @param mixed $primaryVal A scalar for a simple primary key, or an array
     * of column => value pairs for a composite primary key.
     *
     * @return ?RowInterface Returns a Row on success, or `null` on failure.
     *
     */
    public function fetchRow($primaryVal) : ?RowInterface;

    /**
     *
     * Fetches an array of Row objects based on primary-key values, from the
     * identity map as available, and from the database when not.
     *
     * @param mixed $primaryVals An array of primary-key values; each value is
     * scalar for a simple primary key, or an array of column => value pairs for
     * a composite primary key.
     *
     * @return array
     *
     */
    public function fetchRows(array $primaryVals) : array;

    /**
     *
     * Returns a new TableSelect.
     *
     * @param array $whereEquals An array of column-value equality pairs for the
     * WHERE clause.
     *
     * @return TableSelect
     *
     */
    public function select(array $whereEquals = []) : TableSelect;

    /**
     *
     * Inserts a Row into the table.
     *
     * @param RowInterface $row The row to insert.
     *
     * @return bool
     */
    public function insertRow(RowInterface $row) : bool;

    /**
     *
     * Prepares an Insert for a Row.
     *
     * @param RowInterface $row The Row to be inserted.
     *
     * @return InsertInterface
     *
     */
    public function insertRowPrepare(RowInterface $row) : InsertInterface;

    /**
     *
     * Performs the Insert for a Row.
     *
     * @param RowInterface $row The Row to be inserted.
     *
     * @param InsertInterface $insert The Insert to be performed.
     *
     * @return PDOStatement The PDOStatement resulting from the insert.
     *
     */
    public function insertRowPerform(RowInterface $row, InsertInterface $insert) : PDOStatement;

    /**
     *
     * Updates a Row in the table.
     *
     * @param RowInterface $row The row to update.
     *
     * @return bool
     */
    public function updateRow(RowInterface $row) : bool;

    /**
     *
     * Prepares an Update for a Row.
     *
     * @param RowInterface $row The Row to be updated.
     *
     * @return UpdateInterface
     *
     */
    public function updateRowPrepare(RowInterface $row) : UpdateInterface;

    /**
     *
     * Performs the Update for a Row.
     *
     * @param RowInterface $row The Row to be updated.
     *
     * @param UpdateInterface $update The Update to be performed.
     *
     * @return PDOStatement The PDOStatement resulting from the update.
     *
     */
    public function updateRowPerform(RowInterface $row, UpdateInterface $update) : ?PDOStatement;

    /**
     *
     * Deletes a Row from the table.
     *
     * @param RowInterface $row The row to delete.
     *
     * @return bool
     */
    public function deleteRow(RowInterface $row) : bool;

    /**
     *
     * Prepares a Delete for a Row.
     *
     * @param RowInterface $row The Row to be deleted.
     *
     * @return DeleteInterface
     *
     */
    public function deleteRowPrepare(RowInterface $row) : DeleteInterface;

    /**
     *
     * Performs the Delete for a Row.
     *
     * @param RowInterface $row The Row to be deleted.
     *
     * @param DeleteInterface $delete The Delete to be performed.
     *
     * @return PDOStatement The PDOStatement resulting from the delete.
     *
     */
    public function deleteRowPerform(RowInterface $row, DeleteInterface $delete) : PDOStatement;

    /**
     *
     * Returns a new in-memory Row, not identity-mapped.
     *
     * @param array $cols Column values for the Row.
     *
     * @return RowInterface
     *
     */
    public function newRow(array $cols = []) : RowInterface;

    /**
     *
     * Returns a selected Row: if identity mapped already, returns the mapped
     * Row, otherwise returns a new Row and maps it.
     *
     * @param array $cols Column values for the Row.
     *
     * @return RowInterface
     *
     */
    public function getSelectedRow(array $cols) : RowInterface;

    /**
     *
     * Returns the table name.
     *
     * @return string
     *
     */
    public function getName(); // typehinting will break 1.x generated classes

    /**
     *
     * Returns the table column names.
     *
     * @return array
     *
     */
    public function getColNames(); // typehinting will break 1.x generated classes

    /**
     *
     * Returns the table column information.
     *
     * @return array
     *
     */
    public function getCols(); // typehinting will break 1.x generated classes

    /**
     *
     * Returns the primary key column names on the table.
     *
     * @return array The primary key column names.
     *
     */
    public function getPrimaryKey(); // typehinting will break 1.x generated classes

    /**
     *
     * Returns the autoincrement column name on the table, if any.
     *
     * @return string
     *
     */
    public function getAutoinc(); // typehinting will break 1.x generated classes

    /**
     *
     * Returns the default values for a new row.
     *
     * @return array
     *
     */
    public function getColDefaults(); // typehinting will break 1.x generated classes
}
