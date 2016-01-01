<?php
namespace Atlas\Orm\Table;

use Atlas\Orm\Exception;
use Aura\Sql\ConnectionLocator;
use Aura\SqlQuery\QueryFactory;
use Aura\SqlQuery\Common\SelectInterface;

class Gateway
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
        $row = $this->getIdentifiedRow($primaryVal);
        if ($row) {
            return $row;
        }

        $select = $this->select([$this->table->getPrimaryKey() => $primaryVal]);
        return $this->selectRow($select);
    }

    public function fetchRows(array $primaryVals)
    {
        // find identified rows, in the order of the primary values.
        // leave open elements for non-identified rows.
        $rows = [];
        foreach ($primaryVals as $i => $primaryVal) {
            $rows[$primaryVal] = null;
            $row = $this->getIdentifiedRow($primaryVal);
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
        $select = $this
            ->select([$this->table->getPrimaryKey() => $primaryVals])
            ->cols($this->table->getColNames());
        $data = $select->fetchAll();
        foreach ($data as $cols) {
            $row = $this->newSelectedRow($cols);
            $rows[$row->getPrimary()->getVal()] = $row;
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

        if ($this->table->getAutoinc()) {
            $primary = $this->table->getPrimaryKey();
            $row->$primary = $connection->lastInsertId($primary);
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
        $primaryVal = $cols[$this->table->getPrimaryKey()];
        $row = $this->getIdentifiedRow($primaryVal);
        if (! $row) {
            $row = $this->newSelectedRow($cols);
        }
        return $row;
    }

    protected function newPrimary(array &$cols)
    {
        $primaryCol = $this->table->getPrimaryKey();
        $primaryVal = null;
        if (array_key_exists($primaryCol, $cols)) {
            $primaryVal = $cols[$primaryCol];
            unset($cols[$primaryCol]);
        }
        return new Primary([$primaryCol => $primaryVal]);
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
        if ($this->table->getAutoinc()) {
            unset($cols[$this->table->getPrimaryKey()]);
        }
        $insert->cols($cols);

        return $insert;
    }

    protected function newUpdate(RowInterface $row)
    {
        $update = $this->queryFactory->newUpdate();
        $update->table($this->table->getName());

        $cols = $row->getArrayDiff($this->identityMap->getInitial($row));
        unset($cols[$this->table->getPrimaryKey()]);
        $update->cols($cols);

        $primaryCol = $this->table->getPrimaryKey();
        $update->where("{$primaryCol} = ?", $row->getPrimary()->getVal());

        return $update;
    }

    protected function newDelete(RowInterface $row)
    {
        $delete = $this->queryFactory->newDelete();
        $delete->from($this->table->getName());

        $primaryCol = $this->table->getPrimaryKey();
        $delete->where("{$primaryCol} = ?", $row->getPrimary()->getVal());

        return $delete;
    }

    protected function getIdentifiedRow($primaryVal)
    {
        $primary = [$this->table->getPrimaryKey() => $primaryVal];
        return $this->identityMap->getRowByPrimary($primary);
    }

}
