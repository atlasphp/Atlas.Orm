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
abstract class AbstractTable
{
    use AbstractTableTrait;

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

    protected $rowFilter;

    protected $rowFactory;

    protected $identityMap;

    public function __construct(
        ConnectionLocator $connectionLocator,
        QueryFactory $queryFactory,
        IdentityMap $identityMap,
        AbstractRowFactory $rowFactory,
        AbstractRowFilter $rowFilter
    ) {
        $this->connectionLocator = $connectionLocator;
        $this->queryFactory = $queryFactory;
        $this->identityMap = $identityMap;
        $this->rowFactory = $rowFactory;
        $this->rowFilter = $rowFilter;
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

    /**
     *
     * Returns a new Select object.
     *
     * @return TableSelect
     *
     */
    public function select(array $colsVals = [])
    {
        $select = $this->newTableSelect()->from($this->tableName());

        foreach ($colsVals as $col => $val) {
            $this->selectWhere($select, $col, $val);
        }

        return $select;
    }

    protected function newTableSelect()
    {
        return new TableSelect($this, $this->queryFactory->newSelect());
    }

    protected function selectWhere($select, $col, $val)
    {
        $col = $this->tableName() . '.' . $col;

        if (is_array($val)) {
            return $select->where("{$col} IN (?)", $val);
        }

        if ($val === null) {
            return $select->where("{$col} IS NULL");
        }

        return $select->where("{$col} = ?", $val);
    }

    public function fetchRow($primaryVal)
    {
        $primaryIdentity = $this->getPrimaryIdentity($primaryVal);
        $row = $this->identityMap->getRowByPrimary($this->rowFactory->getRowClass(), $primaryIdentity);
        if (! $row) {
            $row = $this->select($primaryIdentity)->fetchRow();
        }
        return $row;
    }

    public function getPrimaryIdentity($primaryVal)
    {
        return [$this->tablePrimary() => $primaryVal];
    }

    public function fetchRowBy(array $colsVals)
    {
        return $this->select($colsVals)->fetchRow();
    }

    /*
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
    public function fetchRowSet(array $primaryVals)
    {
        // pre-empt working on empty array
        if (! $primaryVals) {
            return array();
        }

        $rows = [];
        $this->fillExistingRows($primaryVals, $rows);
        $this->fillMissingRows($primaryVals, $rows);

        // remove unfound rows
        foreach ($rows as $key => $val) {
            if ($val === null) {
                unset($rows[$key]);
            }
        }

        // anything left?
        if (! $rows) {
            return array();
        }

        return $this->rowFactory->newRowSet(array_values($rows));
    }

    // get existing rows from identity map
    protected function fillExistingRows(&$primaryVals, &$rows)
    {
        foreach ($primaryVals as $i => $primaryVal) {
            $rows[$primaryVal] = null;
            $primaryIdentity = $this->getPrimaryIdentity($primaryVal);
            if ($this->identityMap->hasPrimary($this->rowFactory->getRowClass(), $primaryIdentity)) {
                $rows[$primaryVal] = $this->identityMap->getRowByPrimary($this->rowFactory->getRowClass(), $primaryIdentity);
                unset($primaryVals[$i]);
            }
        }
    }

    // get missing rows from database
    protected function fillMissingRows(&$primaryVals, &$rows)
    {
        // are there still rows to fetch?
        if (! $primaryVals) {
            return;
        }
        // fetch and retain remaining rows
        $colsVals = [$this->tablePrimary() => $primaryVals];
        $select = $this->select($colsVals);
        $data = $select->cols($this->tableCols())->fetchAll();
        foreach ($data as $cols) {
            $row = $this->rowFactory->newRow($cols);
            $this->identityMap->setRow($row, $cols);
            $rows[$row->getIdentity()->getVal()] = $row;
        }
    }

    public function fetchRowSetBy(array $colsVals)
    {
        return $this->select($colsVals)->fetchRowSet();
    }

    public function newRow(array $cols)
    {
        return $this->rowFactory->newRow($cols);
    }

    public function newRowSet(array $rows)
    {
        return $this->rowFactory->newRowSet($rows);
    }

    /**
     *
     * Inserts a row through the gateway.
     *
     * @param Row $row The row to insert.
     *
     * @return bool
     *
     */
    public function insert(AbstractRow $row)
    {
        $this->rowFactory->assertRowClass($row);
        $this->rowFilter->forInsert($this, $row);

        $insert = $this->newInsert($row);
        $writeConnection = $this->getWriteConnection();
        $pdoStatement = $writeConnection->perform(
            $insert->getStatement(),
            $insert->getBindValues()
        );

        if (! $pdoStatement->rowCount()) {
            throw Exception::unexpectedRowCountAffected(0);
        }

        $primary = $this->tablePrimary();
        if ($this->tableAutoinc()) {
            $row->$primary = $writeConnection->lastInsertId($primary);
        }

        // @todo add support for "returning" into the row

        // set into the identity map
        $this->identityMap->setRow($row, $row->getArrayCopy());

        return true;
    }

    protected function newInsert(AbstractRow $row)
    {
        $insert = $this->queryFactory->newInsert();
        $insert->into($this->tableName());
        $this->newInsertCols($insert, $row);
        return $insert;
    }

    protected function newInsertCols(Insert $insert, AbstractRow $row)
    {
        $cols = $row->getArrayCopy();
        if ($this->tableAutoinc()) {
            unset($cols[$this->tablePrimary()]);
        }
        $insert->cols($cols);
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
    public function update(AbstractRow $row)
    {
        $this->rowFactory->assertRowClass($row);
        $this->rowFilter->forUpdate($this, $row);

        $update = $this->newUpdate($row);
        if (! $update->hasCols()) {
            return null;
        }

        $pdoStatement = $this->getWriteConnection()->perform(
            $update->getStatement(),
            $update->getBindValues()
        );

        $rowCount = $pdoStatement->rowCount();
        if ($rowCount != 1) {
            throw Exception::unexpectedRowCountAffected($rowCount);
        }

        // reinitialize the initial data for later updates
        $this->identityMap->setInitial($row);

        // @todo add support for "returning" into the row
        return true;
    }

    protected function newUpdate(AbstractRow $row)
    {
        $update = $this->queryFactory->newUpdate();
        $update->table($this->tableName());
        $this->newUpdateCols($update, $row);
        $this->newUpdateWhere($update, $row);
        return $update;
    }

    protected function newUpdateCols(Update $update, AbstractRow $row)
    {
        $cols = $row->getArrayDiff($this->identityMap->getInitial($row));
        unset($cols[$this->tablePrimary()]);
        $update->cols($cols);
    }

    protected function newUpdateWhere(Update $update, AbstractRow $row)
    {
        $primaryCol = $this->tablePrimary();
        $update->where("{$primaryCol} = ?", $row->getIdentity()->getVal());
    }

    /**
     *
     * Deletes a row through the gateway.
     *
     * @param object $row The row to delete.
     *
     * @return bool
     *
     */
    public function delete(AbstractRow $row)
    {
        $this->rowFactory->assertRowClass($row);
        $this->rowFilter->forDelete($this, $row);

        $delete = $this->newDelete($row);
        $pdoStatement = $this->getWriteConnection()->perform(
            $delete->getStatement(),
            $delete->getBindValues()
        );

        $rowCount = $pdoStatement->rowCount();
        if (! $rowCount) {
            return null;
        }

        if ($rowCount != 1) {
            throw Exception::unexpectedRowCountAffected($rowCount);
        }

        return true;
    }

    protected function newDelete(AbstractRow $row)
    {
        $delete = $this->queryFactory->newDelete();
        $delete->from($this->tableName());
        $this->newDeleteWhere($delete, $row);
        return $delete;
    }

    protected function newDeleteWhere(Delete $delete, AbstractRow $row)
    {
        $primaryCol = $this->tablePrimary();
        $delete->where("{$primaryCol} = ?", $row->getIdentity()->getVal());
    }

    public function getMappedOrNewRow(array $cols)
    {
        $primaryVal = $cols[$this->tablePrimary()];
        $primaryIdentity = $this->getPrimaryIdentity($primaryVal);
        $row = $this->identityMap->getRowByPrimary($this->rowFactory->getRowClass(), $primaryIdentity);
        if (! $row) {
            $row = $this->newRow($cols);
            $this->identityMap->setRow($row, $cols);
        }
        return $row;
    }

    public function getMappedOrNewRowSet(array $data)
    {
        $rows = [];
        foreach ($data as $cols) {
            $rows[] = $this->getMappedOrNewRow($cols);
        }

        return $this->newRowSet($rows);
    }
}
