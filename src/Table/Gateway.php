<?php
namespace Atlas\Orm\Table;

use Atlas\Orm\Exception;
use Aura\Sql\ConnectionLocator;
use Aura\SqlQuery\QueryFactory;
use Aura\SqlQuery\Common\SelectInterface;

class Gateway implements GatewayInterface
{
    protected $connectionLocator;
    protected $table;
    protected $queryFactory;
    protected $identityMap;
    protected $readConnection;
    protected $writeConnection;

    public function __construct(
        ConnectionLocator $connectionLocator,
        QueryFactory $queryFactory,
        TableInterface $table,
        IdentityMap $identityMap
    ) {
        $this->connectionLocator = $connectionLocator;
        $this->queryFactory = $queryFactory;
        $this->table = $table;
        $this->identityMap = $identityMap;
    }

    public function getTable()
    {
        return $this->table;
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
        $primary = $this->table->calcPrimary($primaryVal);
        $row = $this->identityMap->getRowByPrimary($primary);
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
            $rows[$primaryVal] = null;
            $primary = $this->table->calcPrimary($primaryVal);
            $row = $this->identityMap->getRowByPrimary($primary);
            if ($row) {
                $rows[$primaryVal] = $row;
                unset($primaryVals[$i]);
            }
        }

        // are there still rows to fetch?
        if (! $primaryVals) {
            // no, all are identified already
            return array_values($rows);
        }

        // fetch and retain remaining rows
        $primaryKey = $this->table->getPrimaryKey();
        $primaryCol = $primaryKey[0];
        $select = $this
            ->select([$primaryCol => $primaryVals])
            ->cols($this->table->getColNames());
        $data = $select->fetchAll();
        foreach ($data as $cols) {
            $row = $this->newSelectedRow($cols);
            $primary = $row->getPrimary()->getArrayCopy();
            $primaryVal = current($primary);
            $rows[$primaryVal] = $row;
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

    public function select(array $colsVals = [])
    {
        return new GatewaySelect(
            $this->newSelect($colsVals),
            $this->getReadConnection(),
            $this->table->getColNames()
        );
    }

    public function selectRow(GatewaySelect $select)
    {
        $cols = $select->cols($this->table->getColNames())->fetchOne();
        if (! $cols) {
            return false;
        }
        return $this->getSelectedRow($cols);
    }

    public function selectRows(GatewaySelect $select)
    {
        $data = $select->cols($this->table->getColNames())->fetchAll();
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

        if (! $pdoStatement->rowCount()) {
            throw Exception::unexpectedRowCountAffected(0);
        }

        $autoinc = $this->getAutoinc();
        if ($autoinc) {
            $row->$autoinc = $connection->lastInsertId($autoinc);
        }

        $after($this, $row, $insert, $pdoStatement);

        $row->setStatus($row::IS_INSERTED);
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

        $row->setStatus($row::IS_UPDATED);
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
        if (! $rowCount) {
            return false;
        }

        if ($rowCount != 1) {
            throw Exception::unexpectedRowCountAffected($rowCount);
        }

        $after($this, $row, $delete, $pdoStatement);

        $row->setStatus($row::IS_DELETED);
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
        $cols = array_merge($this->table->getColDefaults(), $cols);
        $primary = $this->newPrimary($cols);
        $rowClass = $this->table->getRowClass();
        $row = new $rowClass($primary, $cols);
        return $row;
    }

    public function newSelectedRow(array $cols)
    {
        $row = $this->newRow($cols);
        $row->setStatus($row::IS_CLEAN);
        $this->identityMap->setRow($row, $cols);
        return $row;
    }

    public function getSelectedRow(array $cols)
    {
        $primary = $this->table->calcPrimary($cols);
        $row = $this->identityMap->getRowByPrimary($primary);
        if (! $row) {
            $row = $this->newSelectedRow($cols);
        }
        return $row;
    }

    protected function newPrimary(array &$cols)
    {
        $primary = [];
        foreach ($this->table->getPrimaryKey() as $primaryCol) {
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
        $table = $this->table->getName();
        $select->from($table);
        foreach ($colsVals as $col => $val) {
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
        $insert->into($this->table->getName());

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
        $update->table($this->table->getName());

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
        $delete->from($this->table->getName());

        $primary = $row->getPrimary()->getArrayCopy();
        foreach ($primary as $primaryCol => $primaryVal) {
            $delete->where("{$primaryCol} = ?", $primaryVal);
        }

        return $delete;
    }

    // temp to allow for string *or* bool autoinc
    protected function getAutoinc()
    {
        $autoinc = $this->table->getAutoinc();
        if (is_string($autoinc)) {
            return $autoinc;
        }

        if (! $autoinc) {
            return false;
        }

        return current($this->table->getPrimaryKey());
    }
}
