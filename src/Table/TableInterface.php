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
use Aura\SqlQuery\Common\DeleteInterface;
use Aura\SqlQuery\Common\InsertInterface;
use Aura\SqlQuery\Common\SelectInterface;
use Aura\SqlQuery\Common\UpdateInterface;

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
    public function getReadConnection();

    /**
     *
     * Returns the database write connection.
     *
     * @return ExtendedPdoInterface
     *
     */
    public function getWriteConnection();

    /**
     *
     * Fetches one Row based on a primary-key value, from the identity map if
     * present, or from the database if not.
     *
     * @param mixed $primaryVal A scalar for a simple primary key, or an array
     * of column => value pairs for a composite primary key.
     *
     * @return Row|false Returns a Row on success, or `false` on failure.
     *
     */
    public function fetchRow($primaryVal);

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
    public function fetchRows(array $primaryVals);

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
    public function select(array $whereEquals = []);

    /**
     *
     * Inserts a Row into the table.
     *
     * @param RowInterface $row The row to insert.
     *
     */
    public function insertRow(RowInterface $row);

    /**
     *
     * Prepares an Insert for a Row.
     *
     * @param RowInterface $row The Row to be inserted.
     *
     * @return InsertInterface
     *
     */
    public function insertRowPrepare(RowInterface $row);

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
    public function insertRowPerform(RowInterface $row, InsertInterface $insert);

    /**
     *
     * Updates a Row in the table.
     *
     * @param RowInterface $row The row to update.
     *
     */
    public function updateRow(RowInterface $row);

    /**
     *
     * Prepares an Update for a Row.
     *
     * @param RowInterface $row The Row to be updated.
     *
     * @return UpdateInterface
     *
     */
    public function updateRowPrepare(RowInterface $row);

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
    public function updateRowPerform(RowInterface $row, UpdateInterface $update);

    /**
     *
     * Deletes a Row from the table.
     *
     * @param RowInterface $row The row to delete.
     *
     */
    public function deleteRow(RowInterface $row);

    /**
     *
     * Prepares a Delete for a Row.
     *
     * @param RowInterface $row The Row to be deleted.
     *
     * @return DeleteInterface
     *
     */
    public function deleteRowPrepare(RowInterface $row);

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
    public function deleteRowPerform(RowInterface $row, DeleteInterface $delete);

    /**
     *
     * Returns a new in-memory Row, not identity-mapped.
     *
     * @param array $cols Column values for the Row.
     *
     * @return RowInterface
     *
     */
    public function newRow(array $cols = []);

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
