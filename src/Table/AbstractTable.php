<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Orm\Table;

use Atlas\Orm\Exception;
use Aura\Sql\ExtendedPdoInterface;
use Aura\SqlQuery\Common\DeleteInterface;
use Aura\SqlQuery\Common\InsertInterface;
use Aura\SqlQuery\Common\SelectInterface;
use Aura\SqlQuery\Common\UpdateInterface;
use Aura\SqlQuery\QueryFactory;
use PDOStatement;

/**
 *
 * A table data gateway to return Row objects.
 *
 * @package atlas/orm
 *
 */
abstract class AbstractTable implements TableInterface
{
    /**
     *
     * A locator for database connections.
     *
     * @var ConnectionManager
     *
     */
    protected $connectionManager;

    /**
     *
     * A factory for SQL query objects.
     *
     * @var QueryFactory
     *
     */
    protected $queryFactory;

    /**
     *
     * An identity map for Row objects from this table.
     *
     * @var IdentityMap
     *
     */
    protected $identityMap;

    /**
     *
     * A memo of the primary key column(s) for calculating identities.
     *
     * @var string|array A string for simple keys; an array for composite keys.
     *
     */
    protected $identityKey;

    /**
     *
     * Events to invoke during Table operations.
     *
     * @var TableEventsInterface
     *
     */
    protected $events;

    /**
     *
     * Constructor.
     *
     * @param ConnectionManager $connectionManager A manager for database
     * connections.
     *
     * @param QueryFactory $queryFactory A factory for SQL query objects.
     *
     * @param IdentityMap $identityMap An identity map of Row objects.
     *
     * @param TableEventsInterface $events Events to invoke during Table
     * operations.
     *
     */
    public function __construct(
        ConnectionManager $connectionManager,
        QueryFactory $queryFactory,
        IdentityMap $identityMap,
        TableEventsInterface $events
    ) {
        $this->connectionManager = $connectionManager;
        $this->queryFactory = $queryFactory;
        $this->identityMap = $identityMap;
        $this->events = $events;

        $this->identityKey = $this->getPrimaryKey();
        if (count($this->identityKey) == 1) {
            $this->identityKey = current($this->identityKey);
        }
    }

    /**
     *
     * Returns the database read connection.
     *
     * @return ExtendedPdoInterface
     *
     */
    public function getReadConnection() : ExtendedPdoInterface
    {
        return $this->connectionManager->getRead(static::CLASS);
    }

    /**
     *
     * Returns the database write connection.
     *
     * @return ExtendedPdoInterface
     *
     */
    public function getWriteConnection() : ExtendedPdoInterface
    {
        return $this->connectionManager->getWrite(static::CLASS);
    }

    /**
     *
     * Fetches one Row based on a primary-key value, from the identity map if
     * present, or from the database if not.
     *
     * @param mixed $primaryVal A scalar for a simple primary key, or an array
     * of column => value pairs for a composite primary key.
     *
     * @return ?RowInterface
     *
     */
    public function fetchRow($primaryVal) : ?RowInterface
    {
        $primary = $this->calcIdentity($primaryVal);
        $row = $this->identityMap->getRow($primary);
        if ($row) {
            return $row;
        }

        return $this->select($primary)->fetchRow();
    }

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
    public function fetchRows(array $primaryVals) : array
    {
        // find identified rows, in the order of the primary values.
        // leave open elements for non-identified rows.
        $rows = [];
        foreach ($primaryVals as $i => $primaryVal) {
            $primary = $this->calcIdentity($primaryVal);
            $serial = $this->identityMap->getSerial($primary);
            $rows[$serial] = null;
            $row = $this->identityMap->getRow($primary);
            if ($row) {
                $rows[$serial] = $row;
                unset($primaryVals[$i]);
            }
        }

        // are there still rows to fetch?
        if (! $primaryVals) {
            // no, all are identified already
            return array_values($rows);
        }

        // fetch and retain remaining rows
        $select = $this->select()->cols($this->getColNames());
        $this->selectWherePrimary($select, $primaryVals);
        $data = $select->fetchAll();
        foreach ($data as $cols) {
            $row = $this->getSelectedRow($cols);
            $primary = $this->calcIdentity($cols);
            $serial = $this->identityMap->getSerial($primary);
            $rows[$serial] = $row;
        }

        // remove unfound rows
        foreach ($rows as $key => $val) {
            if ($val === null) {
                unset($rows[$key]);
            }
        }

        // done
        return array_values($rows);
    }

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
    public function select(array $whereEquals = []) : TableSelect
    {
        $select = $this->queryFactory->newSelect();

        $table = $this->getName();
        $select->from($table);
        foreach ($whereEquals as $col => $val) {
            if (is_numeric($col)) {
                throw Exception::numericCol($col);
            }
            $this->selectWhere($select, "{$table}.{$col}", $val);
        }

        return new TableSelect($this, $select);
    }

    /**
     *
     * Returns a new Insert object for this table.
     *
     * @return InsertInterface
     *
     */
    public function insert() : InsertInterface
    {
        $insert = $this->queryFactory->newInsert();
        $insert->into($this->getName());
        return $insert;
    }

    /**
     *
     * Returns a new Update object for this table.
     *
     * @return UpdateInterface
     *
     */
    public function update() : UpdateInterface
    {
        $update = $this->queryFactory->newUpdate();
        $update->table($this->getName());
        return $update;
    }

    /**
     *
     * Returns a new Delete object for this table.
     *
     * @return DeleteInterface
     *
     */
    public function delete() : DeleteInterface
    {
        $delete = $this->queryFactory->newDelete();
        $delete->from($this->getName());
        return $delete;
    }

    /**
     *
     * Inserts a Row into the table.
     *
     * @param RowInterface $row The row to insert.
     *
     * @return bool
     *
     */
    public function insertRow(RowInterface $row) : bool
    {
        $insert = $this->insertRowPrepare($row);
        return (bool) $this->insertRowPerform($row, $insert);
    }

    /**
     *
     * Prepares an Insert for a Row.
     *
     * @param RowInterface $row The Row to be inserted.
     *
     * @return InsertInterface
     *
     */
    public function insertRowPrepare(RowInterface $row) : InsertInterface
    {
        $this->events->beforeInsert($this, $row);

        $insert = $this->insert();
        $cols = $row->getArrayCopy();
        $autoinc = $this->getAutoinc();
        if ($autoinc) {
            unset($cols[$autoinc]);
        }
        $insert->cols($cols);

        $this->events->modifyInsert($this, $row, $insert);
        return $insert;
    }

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
    public function insertRowPerform(RowInterface $row, InsertInterface $insert) : PDOStatement
    {
        $connection = $this->getWriteConnection();
        $pdoStatement = $connection->perform(
            $insert->getStatement(),
            $insert->getBindValues()
        );

        $rowCount = $pdoStatement->rowCount();
        if ($rowCount != 1) {
            throw Exception::unexpectedRowCountAffected($rowCount);
        }

        $autoinc = $this->getAutoinc();
        if ($autoinc) {
            $lastInsertIdName = $insert->getLastInsertIdName($autoinc);
            $row->$autoinc = $connection->lastInsertId($lastInsertIdName);
        }

        $this->events->afterInsert($this, $row, $insert, $pdoStatement);

        $row->setStatus($row::INSERTED);
        $this->identityMap->setRow($row, $row->getArrayCopy(), $this->getPrimaryKey());

        return $pdoStatement;
    }

    /**
     *
     * Updates a Row in the table.
     *
     * @param RowInterface $row The row to update.
     *
     * @return bool
     *
     */
    public function updateRow(RowInterface $row) : bool
    {
        $update = $this->updateRowPrepare($row);
        return (bool) $this->updateRowPerform($row, $update);

    }

    /**
     *
     * Prepares an Update for a Row.
     *
     * @param RowInterface $row The Row to be updated.
     *
     * @return UpdateInterface
     *
     */
    public function updateRowPrepare(RowInterface $row) : UpdateInterface
    {
        $this->events->beforeUpdate($this, $row);

        $update = $this->update();
        $init = $this->identityMap->getInitial($row);
        $diff = $row->getArrayDiff($init);
        foreach ($this->getPrimaryKey() as $primaryCol) {
            if (array_key_exists($primaryCol, $diff)) {
                $message = "Primary key value for '$primaryCol' "
                    . "changed from '$init[$primaryCol]' "
                    . "to '$diff[$primaryCol]'.";
                throw new Exception($message);
            }
            $update->where("{$primaryCol} = ?", $row->$primaryCol);
            unset($diff[$primaryCol]);
        }
        $update->cols($diff);

        $this->events->modifyUpdate($this, $row, $update);
        return $update;
    }

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
    public function updateRowPerform(RowInterface $row, UpdateInterface $update) : ?PDOStatement
    {
        if (! $update->hasCols()) {
            return null;
        }

        $connection = $this->getWriteConnection();
        $pdoStatement = $connection->perform(
            $update->getStatement(),
            $update->getBindValues()
        );

        $rowCount = $pdoStatement->rowCount();
        if ($rowCount != 1) {
            throw Exception::unexpectedRowCountAffected($rowCount);
        }

        $this->events->afterUpdate($this, $row, $update, $pdoStatement);

        $row->setStatus($row::UPDATED);
        $this->identityMap->resetInitial($row);

        return $pdoStatement;
    }

    /**
     *
     * Deletes a Row from the table.
     *
     * @param RowInterface $row The row to delete.
     *
     * @return bool
     *
     */
    public function deleteRow(RowInterface $row) : bool
    {
        $delete = $this->deleteRowPrepare($row);
        return (bool) $this->deleteRowPerform($row, $delete);
    }

    /**
     *
     * Prepares a Delete for a Row.
     *
     * @param RowInterface $row The Row to be deleted.
     *
     * @return DeleteInterface
     *
     */
    public function deleteRowPrepare(RowInterface $row) : DeleteInterface
    {
        $this->events->beforeDelete($this, $row);

        $delete = $this->delete();
        foreach ($this->getPrimaryKey() as $primaryCol) {
            $delete->where("{$primaryCol} = ?", $row->$primaryCol);
        }

        $this->events->modifyDelete($this, $row, $delete);
        return $delete;
    }

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
    public function deleteRowPerform(RowInterface $row, DeleteInterface $delete) : PDOStatement
    {
        $connection = $this->getWriteConnection();
        $pdoStatement = $connection->perform(
            $delete->getStatement(),
            $delete->getBindValues()
        );

        $rowCount = $pdoStatement->rowCount();
        if ($rowCount != 1) {
            throw Exception::unexpectedRowCountAffected($rowCount);
        }

        $this->events->afterDelete($this, $row, $delete, $pdoStatement);

        $row->setStatus($row::DELETED);
        return $pdoStatement;
    }

    /**
     *
     * Returns a new in-memory Row, not identity-mapped.
     *
     * @param array $cols Column values for the Row.
     *
     * @return RowInterface
     *
     */
    public function newRow(array $cols = []) : RowInterface
    {
        $colNames = $this->getColNames();
        foreach ($cols as $col => $val) {
            if (! in_array($col, $colNames)) {
                unset($cols[$col]);
            }
        }
        $cols = array_merge($this->getColDefaults(), $cols);
        return new Row($cols);
    }

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
    public function getSelectedRow(array $cols) : RowInterface
    {
        $primary = $this->calcIdentity($cols);
        $row = $this->identityMap->getRow($primary);
        if (! $row) {
            $row = $this->newRow($cols);
            $this->events->modifySelectedRow($this, $row);
            $row->setStatus($row::SELECTED);
            $this->identityMap->setRow($row, $cols, $this->getPrimaryKey());
        }
        return $row;
    }

    /**
     *
     * Returns the table name.
     *
     * @return string
     *
     */
    abstract public function getName(); // typehinting will break 1.x generated classes

    /**
     *
     * Returns the table column names.
     *
     * @return array
     *
     */
    abstract public function getColNames(); // typehinting will break 1.x generated classes

    /**
     *
     * Returns the table column information.
     *
     * @return array
     *
     */
    abstract public function getCols(); // typehinting will break 1.x generated classes

    /**
     *
     * Returns the primary key column names on the table.
     *
     * @return array The primary key column names.
     *
     */
    abstract public function getPrimaryKey(); // typehinting will break 1.x generated classes

    /**
     *
     * Returns the name of the autoincrement column, if any.
     *
     * @return string
     *
     */
    abstract public function getAutoinc(); // typehinting will break 1.x generated classes

    /**
     *
     * Returns the default values for a new row.
     *
     * @return array
     *
     */
    abstract public function getColDefaults(); // typehinting will break 1.x generated classes

    /**
     *
     * Adds the primary-key WHERE conditions to a TableSelect.
     *
     * @param TableSelect $select Add the conditions to this TableSelect.
     *
     * @param array $primaryVals Use these primary-key values.
     *
     */
    protected function selectWherePrimary(TableSelect $select, array $primaryVals) : void
    {
        $primaryKey = $this->getPrimaryKey();
        if (count($primaryKey) == 1) {
            // simple key
            $primaryCol = current($primaryKey);
            $select->where("$primaryCol IN (?)", $primaryVals);
            return;
        }

        // composite key
        foreach ($primaryVals as $primaryVal) {
            $primary = $this->calcIdentity($primaryVal);
            $cols = array_keys($primary);
            $vals = array_values($primary);
            $cond = implode(' = ? AND ', $cols) . ' = ?';
            $select->orWhere($cond, ...$vals);
        }
    }

    /**
     *
     * Adds a WHERE condition to a select.
     *
     * @param SelectInterface $select The query object.
     *
     * @param string $col The column name.
     *
     * @param mixed $val The column value.
     *
     */
    protected function selectWhere(SelectInterface $select, string $col, $val) : void
    {
        if (is_array($val)) {
            $select->where("{$col} IN (?)", $val);
            return;
        }

        if ($val === null) {
            $select->where("{$col} IS NULL");
            return;
        }

        $select->where("{$col} = ?", $val);
    }

    /**
     *
     * Calculate the identity key for the identity map.
     *
     * @param mixed $primaryVal The primary-key value.
     *
     * @return array
     *
     */
    protected function calcIdentity($primaryVal) : array
    {
        if (is_array($this->identityKey)) {
            return $this->calcIdentityComposite($primaryVal);
        }

        if (is_array($primaryVal) && isset($primaryVal[$this->identityKey])) {
            $primaryVal = $primaryVal[$this->identityKey];
        }

        if (! is_scalar($primaryVal)) {
            throw Exception::primaryValueNotScalar($this->identityKey, $primaryVal);
        }

        return [$this->identityKey => $primaryVal];
    }

    /**
     *
     * Calculate a composite identity key for the identity map.
     *
     * @param array $primaryVal The primary-key value.
     *
     * @return array
     *
     */
    protected function calcIdentityComposite(array $primaryVal) : array
    {
        $primary = [];
        foreach ($this->identityKey as $col) {
            if (! isset($primaryVal[$col])) {
                throw Exception::primaryValueMissing($col);
            }
            if (! is_scalar($primaryVal[$col])) {
                throw Exception::primaryValueNotScalar($col, $primaryVal[$col]);
            }
            $primary[$col] = $primaryVal[$col];
        }
        return $primary;
    }
}
