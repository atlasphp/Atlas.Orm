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
use Aura\Sql\ConnectionLocator;
use Aura\SqlQuery\QueryFactory;
use Aura\SqlQuery\Common\SelectInterface;

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
     * @var ConnectionLocator
     *
     */
    protected $connectionLocator;

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
     * The "read" database connection.
     *
     * @var ExtendedPdoInterface
     *
     */
    protected $readConnection;

    /**
     *
     * The "write" database connection.
     *
     * @var ExtendedPdoInterface
     *
     */
    protected $writeConnection;

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
     * @param ConnectionLocator $connectionLocator A locator for database
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
        ConnectionLocator $connectionLocator,
        QueryFactory $queryFactory,
        IdentityMap $identityMap,
        TableEventsInterface $events
    ) {
        $this->connectionLocator = $connectionLocator;
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
    public function getReadConnection()
    {
        if (! $this->readConnection) {
            $this->readConnection = $this->connectionLocator->getRead();
        }
        return $this->readConnection;
    }

    /**
     *
     * Returns the database write connection.
     *
     * @return ExtendedPdoInterface
     *
     */
    public function getWriteConnection()
    {
        if (! $this->writeConnection) {
            $this->writeConnection = $this->connectionLocator->getWrite();
        }
        return $this->writeConnection;
    }

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
    public function fetchRow($primaryVal)
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
    public function fetchRows(array $primaryVals)
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
    public function select(array $whereEquals = [])
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
     * @return Insert
     *
     */
    public function insert()
    {
        $insert = $this->queryFactory->newInsert();
        $insert->into($this->getName());
        return $insert;
    }

    /**
     *
     * Returns a new Update object for this table.
     *
     * @return Update
     *
     */
    public function update()
    {
        $update = $this->queryFactory->newUpdate();
        $update->table($this->getName());
        return $update;
    }

    /**
     *
     * Returns a new Delete object for this table.
     *
     * @return Delete
     *
     */
    protected function delete()
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
     */
    public function insertRow(RowInterface $row)
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
            $row->$autoinc = $connection->lastInsertId($autoinc);
        }

        $this->events->afterInsert($this, $row, $insert, $pdoStatement);

        $row->setStatus($row::INSERTED);
        $this->identityMap->setRow($row, $row->getArrayCopy(), $this->getPrimaryKey());

        return true;
    }

    /**
     *
     * Updates a Row in the table.
     *
     * @param RowInterface $row The row to update.
     *
     */
    public function updateRow(RowInterface $row)
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

        if (! $update->hasCols()) {
            return false;
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

        return true;
    }

    /**
     *
     * Deletes a Row from the table.
     *
     * @param RowInterface $row The row to delete.
     *
     */
    public function deleteRow(RowInterface $row)
    {
        $this->events->beforeDelete($this, $row);

        $delete = $this->delete();
        foreach ($this->getPrimaryKey() as $primaryCol) {
            $delete->where("{$primaryCol} = ?", $row->$primaryCol);
        }

        $this->events->modifyDelete($this, $row, $delete);

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
        return true;
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
    public function newRow(array $cols = [])
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
    public function getSelectedRow(array $cols)
    {
        $primary = $this->calcIdentity($cols);
        $row = $this->identityMap->getRow($primary);
        if (! $row) {
            $row = $this->newRow($cols);
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
     * Returns the name of the autoincrement column, if any.
     *
     * @return string
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
     * Adds the primary-key WHERE conditions to a TableSelect.
     *
     * @param TableSelect $select Add the conditions to this TableSelect.
     *
     * @param array $primaryVals Use these primary-key values.
     *
     */
    protected function selectWherePrimary(TableSelect $select, array $primaryVals)
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
    protected function selectWhere(SelectInterface $select, $col, $val)
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
    protected function calcIdentity($primaryVal)
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
    protected function calcIdentityComposite(array $primaryVal)
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
