<?php
namespace Atlas\Orm\Table;

use Atlas\Orm\Exception;
use Aura\Sql\ConnectionLocator;
use Aura\SqlQuery\QueryFactory;
use Aura\SqlQuery\Common\SelectInterface;

abstract class AbstractTable implements TableInterface
{
    protected $connectionLocator;
    protected $queryFactory;
    protected $identityMap;
    protected $readConnection;
    protected $writeConnection;
    protected $primaryKey;

    public function __construct(
        ConnectionLocator $connectionLocator,
        QueryFactory $queryFactory,
        IdentityMap $identityMap
    ) {
        $this->connectionLocator = $connectionLocator;
        $this->queryFactory = $queryFactory;
        $this->identityMap = $identityMap;

        $this->primaryKey = $this->getPrimaryKey();
        if (count($this->primaryKey) == 1) {
            $this->primaryKey = current($this->primaryKey);
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

    public function fetchRow($primaryVal)
    {
        $primary = $this->calcPrimary($primaryVal);
        $row = $this->identityMap->getRow($primary);
        if ($row) {
            return $row;
        }

        $select = $this->select($primary);
        return $this->selectRow($select);
    }

    public function fetchRows(array $primaryVals)
    {
        // find identified rows, in the order of the primary values.
        // leave open elements for non-identified rows.
        $rows = [];
        foreach ($primaryVals as $i => $primaryVal) {
            $primary = $this->calcPrimary($primaryVal);
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
            $row = $this->newSelectedRow($cols);
            $primary = $this->calcPrimary($cols);
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

    // need to expose this as public,
    // and wrap in parens so that other conditions are honored.
    // or place it on the Select itself?
    protected function selectWherePrimary($select, $primaryVals)
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
            $primary = $this->calcPrimary($primaryVal);
            $cols = array_keys($primary);
            $vals = array_values($primary);
            $cond = implode(' = ? AND ', $cols) . ' = ?';
            $select->orWhere($cond, ...$vals);
        }
    }

    public function select(array $colsVals = [])
    {
        return new TableSelect(
            $this->newSelect($colsVals),
            $this->getReadConnection(),
            $this->getColNames()
        );
    }

    public function selectRow(TableSelect $select)
    {
        $cols = $select->cols($this->getColNames())->fetchOne();
        if (! $cols) {
            return false;
        }
        return $this->getSelectedRow($cols);
    }

    public function selectRows(TableSelect $select)
    {
        $data = $select->cols($this->getColNames())->fetchAll();
        if (! $data) {
            return [];
        }

        $rows = [];
        foreach ($data as $cols) {
            $rows[] = $this->getSelectedRow($cols);
        }

        return $rows;
    }

    public function insert(RowInterface $row, callable $modify, callable $after)
    {
        $insert = $this->newInsert($row);
        $modify($this, $row, $insert);

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

        $after($this, $row, $insert, $pdoStatement);

        $row->setStatus($row::INSERTED);
        $this->identityMap->setRow($row, $row->getArrayCopy());

        return true;
    }

    public function update(RowInterface $row, callable $modify, callable $after)
    {
        $update = $this->newUpdate($row);
        $modify($this, $row, $update);

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

        $after($this, $row, $update, $pdoStatement);

        $row->setStatus($row::UPDATED);
        $this->identityMap->setInitial($row);

        return true;
    }

    public function delete(RowInterface $row, callable $modify, callable $after)
    {
        $delete = $this->newDelete($row);
        $modify($this, $row, $delete);

        $connection = $this->getWriteConnection();
        $pdoStatement = $connection->perform(
            $delete->getStatement(),
            $delete->getBindValues()
        );

        $rowCount = $pdoStatement->rowCount();
        if ($rowCount != 1) {
            throw Exception::unexpectedRowCountAffected($rowCount);
        }

        $after($this, $row, $delete, $pdoStatement);

        $row->setStatus($row::DELETED);
        return true;
    }

    /**
     *
     * Returns a new Row for the table.
     *
     * @return RowInterface
     *
     */
    public function newRow(array $cols = [])
    {
        $cols = array_merge($this->getColDefaults(), $cols);
        $primary = $this->newPrimary($cols);
        return new Row($primary, $cols);
    }

    public function newSelectedRow(array $cols)
    {
        $row = $this->newRow($cols);
        $row->setStatus($row::SELECTED);
        $this->identityMap->setRow($row, $cols);
        return $row;
    }

    public function getSelectedRow(array $cols)
    {
        $primary = $this->calcPrimary($cols);
        $row = $this->identityMap->getRow($primary);
        if (! $row) {
            $row = $this->newSelectedRow($cols);
        }
        return $row;
    }

    protected function newPrimary(array &$cols)
    {
        $primary = [];
        foreach ($this->getPrimaryKey() as $primaryCol) {
            $primary[$primaryCol] = null;
            if (isset($cols[$primaryCol])) {
                $primary[$primaryCol] = $cols[$primaryCol];
                unset($cols[$primaryCol]);
            }
        }
        return new Primary($primary);
    }

    protected function newSelect(array $colsVals = [])
    {
        $select = $this->queryFactory->newSelect();
        $table = $this->getName();
        $select->from($table);
        foreach ($colsVals as $col => $val) {
            if (is_numeric($col)) {
                throw Exception::numericCol($col);
            }
            $this->selectWhere($select, "{$table}.{$col}", $val);
        }
        return $select;
    }

    protected function selectWhere($select, $col, $val)
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

    protected function newInsert(RowInterface $row)
    {
        $insert = $this->queryFactory->newInsert();
        $insert->into($this->getName());

        $cols = $row->getArrayCopy();
        $autoinc = $this->getAutoinc();
        if ($autoinc) {
            unset($cols[$autoinc]);
        }
        $insert->cols($cols);

        return $insert;
    }

    protected function newUpdate(RowInterface $row)
    {
        $update = $this->queryFactory->newUpdate();
        $update->table($this->getName());

        $cols = $row->getArrayDiff($this->identityMap->getInitial($row));

        $primary = $row->getPrimary()->getArrayCopy();
        foreach ($primary as $primaryCol => $primaryVal) {
            $update->where("{$primaryCol} = ?", $primaryVal);
            unset($cols[$primaryCol]);
        }

        $update->cols($cols);
        return $update;
    }

    protected function newDelete(RowInterface $row)
    {
        $delete = $this->queryFactory->newDelete();
        $delete->from($this->getName());

        $primary = $row->getPrimary()->getArrayCopy();
        foreach ($primary as $primaryCol => $primaryVal) {
            $delete->where("{$primaryCol} = ?", $primaryVal);
        }

        return $delete;
    }

    protected function calcPrimary($primaryVal)
    {
        if (is_array($this->primaryKey)) {
            return $this->calcPrimaryComposite($primaryVal);
        }

        if (is_array($primaryVal) && isset($primaryVal[$this->primaryKey])) {
            $primaryVal = $primaryVal[$this->primaryKey];
        }

        if (! is_scalar($primaryVal)) {
            throw Exception::primaryValueNotScalar($this->primaryKey, $primaryVal);
        }

        return [$this->primaryKey => $primaryVal];
    }

    protected function calcPrimaryComposite($primaryVal)
    {
        if (! is_array($primaryVal)) {
            throw Exception::primaryKeyNotArray($primaryVal);
        }

        $primary = [];
        foreach ($this->primaryKey as $col) {
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
}
