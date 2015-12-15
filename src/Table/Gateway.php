<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @package Atlas.Atlas
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Orm\Table;

use Atlas\Orm\Exception;
use Atlas\Orm\Status;
use Aura\Sql\ConnectionLocator;
use Aura\SqlQuery\QueryFactory;
use Aura\SqlQuery\Common\Delete;
use Aura\SqlQuery\Common\Insert;
use Aura\SqlQuery\Common\Update;

/**
 *
 * A TableDataGateway that returns Row and RowSet objects.
 *
 * @package Atlas.Atlas
 *
 */
class Gateway
{
    /**
     *
     * A database connection locator.
     *
     * @var ConnectionLocator
     *
     */
    protected $connectionLocator;

    /**
     *
     * A factory to create query statements.
     *
     * @var QueryFactory
     *
     */
    protected $queryFactory;

    /**
     *
     * A read connection drawn from the connection locator.
     *
     * @var ExtendedPdoInterface
     *
     */
    protected $readConnection;

    /**
     *
     * A write connection drawn from the connection locator.
     *
     * @var ExtendedPdoInterface
     *
     */
    protected $writeConnection;

    protected $table;

    protected $events;

    protected $identityMap;

    protected $tableClass;

    public function __construct(
        ConnectionLocator $connectionLocator,
        QueryFactory $queryFactory,
        IdentityMap $identityMap,
        TableInterface $table,
        TableEvents $events
    ) {
        $this->connectionLocator = $connectionLocator;
        $this->queryFactory = $queryFactory;
        $this->identityMap = $identityMap;
        $this->table = $table;
        $this->events = $events;
        $this->tableClass = get_class($this->table);
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

    public function getIdentityMap()
    {
        return $this->identityMap;
    }

    public function fetchRow($primaryVal)
    {
        $primaryIdentity = $this->getPrimaryIdentity($primaryVal);
        $row = $this->identityMap->getRowByPrimary(
            $this->tableClass,
            $primaryIdentity
        );
        if (! $row) {
            $row = $this->select($primaryIdentity)->fetchRow();
        }
        return $row;
    }

    public function fetchRowBy(array $colsVals)
    {
        return $this->select($colsVals)->fetchRow();
    }

    public function fetchRowSet(array $primaryVals)
    {
        $rows = $this->identifyRows($primaryVals);
        if (! $rows) {
            return [];
        }

        return $this->newRowSet($rows);
    }

    public function fetchRowSetBy(array $colsVals)
    {
        return $this->select($colsVals)->fetchRowSet();
    }

    public function save(Row $row)
    {
        switch ($row->getStatus()) {
            case Status::IS_NEW:
                return $this->insert($row);
            case Status::IS_DIRTY:
                return $this->update($row);
            case Status::IS_TRASH:
                return $this->delete($row);
        }
        return false;
    }

    /**
     *
     * Returns a new Select object.
     *
     * @return TableSelect
     *
     */
    public function select(array $colsVals = [])
    {
        $select = $this->newTableSelect();
        foreach ($colsVals as $col => $val) {
            $this->selectWhere($select, $col, $val);
        }
        return $select;
    }

    protected function selectWhere(TableSelect $select, $col, $val)
    {
        $col = $this->table->getName() . '.' . $col;

        if (is_array($val)) {
            return $select->where("{$col} IN (?)", $val);
        }

        if ($val === null) {
            return $select->where("{$col} IS NULL");
        }

        $select->where("{$col} = ?", $val);
    }

    /**
     *
     * Inserts a row.
     *
     * @param Row $row The row to insert.
     *
     * @return bool
     *
     */
    public function insert(Row $row)
    {
        $row->assertTableClass($this->tableClass);
        $this->events->beforeInsert($this->table, $row);

        $insert = $this->newInsert($row);
        $connection = $this->getWriteConnection();
        $pdoStatement = $connection->perform(
            $insert->getStatement(),
            $insert->getBindValues()
        );

        if (! $pdoStatement->rowCount()) {
            throw Exception::unexpectedRowCountAffected(0);
        }

        if ($this->table->getAutoinc()) {
            $primary = $this->table->getPrimary();
            $row->$primary = $connection->lastInsertId($primary);
        }

        $this->events->afterInsert($this->table, $row, $insert, $pdoStatement);
        $row->markAsSaved();

        // set into the identity map
        $this->identityMap->setRow($row, $row->getArrayCopy());
        return true;
    }

    /**
     *
     * Updates a row.
     *
     * @param Row $row The row to update.
     *
     * @return bool
     *
     */
    public function update(Row $row)
    {
        $row->assertTableClass($this->tableClass);
        $this->events->beforeUpdate($this->table, $row);

        $update = $this->newUpdate($row);
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

        $this->events->afterUpdate($this->table, $row, $update, $pdoStatement);
        $row->markAsSaved();

        // reinitialize the initial data for later updates
        $this->identityMap->setInitial($row);
        return true;
    }

    /**
     *
     * Deletes a row.
     *
     * @param Row $row The row to delete.
     *
     * @return bool
     *
     */
    public function delete(Row $row)
    {
        $row->assertTableClass($this->tableClass);
        $this->events->beforeDelete($this->table, $row);

        $delete = $this->newDelete($row);
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

        $this->events->afterDelete($this->table, $row, $delete, $pdoStatement);
        $row->markAsDeleted();

        return true;
    }

    public function newRow(array $cols = [])
    {
        $cols = array_merge($this->table->getColDefaults(), $cols);
        $rowIdentity = $this->newRowIdentity($cols);
        $row = new Row($this->tableClass, $rowIdentity, $cols);
        $this->events->modifyNewRow($this->table, $row);
        return $row;
    }

    public function newRowSet(array $rows)
    {
        return new RowSet($this->tableClass, $rows);
    }

    public function newOrIdentifiedRow(array $cols)
    {
        $primaryVal = $cols[$this->table->getPrimary()];
        $primaryIdentity = $this->getPrimaryIdentity($primaryVal);
        $row = $this->identityMap->getRowByPrimary(
            $this->tableClass,
            $primaryIdentity
        );
        if (! $row) {
            $row = $this->newRow($cols);
            $row->markAsClean();
            $this->identityMap->setRow($row, $cols);
        }
        return $row;
    }

    public function newOrIdentifiedRowSet(array $data)
    {
        $rows = [];
        foreach ($data as $cols) {
            $rows[] = $this->newOrIdentifiedRow($cols);
        }
        return $this->newRowSet($rows);
    }

    protected function newTableSelect()
    {
        $select = new TableSelect(
            $this->queryFactory->newSelect(),
            $this->getReadConnection(),
            $this->table->getColNames(),
            [$this, 'newOrIdentifiedRow'],
            [$this, 'newOrIdentifiedRowSet']
        );
        $select->from($this->table->getName());
        return $select;
    }

    protected function newInsert(Row $row)
    {
        $insert = $this->queryFactory->newInsert();
        $insert->into($this->table->getName());

        $cols = $row->getArrayCopy();
        if ($this->table->getAutoinc()) {
            unset($cols[$this->table->getPrimary()]);
        }
        $insert->cols($cols);

        $this->events->modifyInsert($this->table, $row, $insert);
        return $insert;
    }

    protected function newUpdate(Row $row)
    {
        $update = $this->queryFactory->newUpdate();
        $update->table($this->table->getName());

        $cols = $row->getArrayDiff($this->identityMap->getInitial($row));
        unset($cols[$this->table->getPrimary()]);
        $update->cols($cols);

        $primaryCol = $this->table->getPrimary();
        $update->where("{$primaryCol} = ?", $row->getIdentity()->getVal());

        $this->events->modifyUpdate($this->table, $row, $update);
        return $update;
    }

    protected function newDelete(Row $row)
    {
        $delete = $this->queryFactory->newDelete();
        $delete->from($this->table->getName());

        $primaryCol = $this->table->getPrimary();
        $delete->where("{$primaryCol} = ?", $row->getIdentity()->getVal());

        $this->events->modifyDelete($this->table, $row, $delete);
        return $delete;
    }

    protected function newRowIdentity(array &$cols)
    {
        $primaryCol = $this->table->getPrimary();
        $primaryVal = null;
        if (array_key_exists($primaryCol, $cols)) {
            $primaryVal = $cols[$primaryCol];
            unset($cols[$primaryCol]);
        }

        return new RowIdentity([$primaryCol => $primaryVal]);
    }

    protected function getPrimaryIdentity($primaryVal)
    {
        return [$this->table->getPrimary() => $primaryVal];
    }

    /*
    Retrieve rows from identity map and/or database.

    Rows by primary:
        create empty rows
        foreach primary value ...
            add null in rows keyed on primary value to maintain place
            if primary value in map
                retain mapped row in set keyed on primary value
                remove primary value from list
        select remaining primary values
        foreach returned one ...
            new row object
            retain row in map
            add row in set on ID key
        return rows
    */
    protected function identifyRows($primaryVals)
    {
        if (! $primaryVals) {
            return [];
        }

        $rows = [];
        foreach ($primaryVals as $i => $primaryVal) {
            $rows[$primaryVal] = null;
            $primaryIdentity = $this->getPrimaryIdentity($primaryVal);
            $hasPrimary = $this->identityMap->hasPrimary(
                $this->tableClass,
                $primaryIdentity
            );
            if ($hasPrimary) {
                $rows[$primaryVal] = $this->identityMap->getRowByPrimary(
                    $this->tableClass,
                    $primaryIdentity
                );
                unset($primaryVals[$i]);
            }
        }

        // are there still rows to fetch?
        if (! $primaryVals) {
            return array_values($rows);
        }

        // fetch and retain remaining rows
        $colsVals = [$this->table->getPrimary() => $primaryVals];
        $select = $this->select($colsVals);
        $data = $select->cols($this->table->getColNames())->fetchAll();
        foreach ($data as $cols) {
            $row = $this->newRow($cols);
            $this->identityMap->setRow($row, $cols);
            $rows[$row->getIdentity()->getVal()] = $row;
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
}
