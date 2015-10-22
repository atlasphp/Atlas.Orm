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
namespace Atlas\Table;

use Atlas\Exception;
use Aura\Sql\ConnectionLocator;
use Aura\SqlQuery\QueryFactory;
use Aura\SqlQuery\Common\Delete;
use Aura\SqlQuery\Common\Insert;
use Aura\SqlQuery\Common\Update;
use InvalidArgumentException;

/**
 *
 * A TableDataGateway that returns Row and RowSet objects.
 *
 * @todo An assertion to check that Row and RowSet are of the right type.
 *
 * @package Atlas.Atlas
 *
 */
abstract class AbstractTable
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

    abstract public function getTable();

    /**
     *
     * Does the database set the primary key value on insert by autoincrement?
     *
     * @return bool
     *
     */
    abstract public function getAutoinc();

    /**
     *
     * Select these columns.
     *
     * @return array
     *
     */
    abstract public function getCols();

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

    public function getRowFactory()
    {
        return $this->rowFactory;
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
        $select = $this->newTableSelect()->from($this->getTable());

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
        $col = $this->getTable() . '.' . $col;

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
        $row = $this->identityMap->getRowByPrimary($primaryIdentity);
        if (! $row) {
            $row = $this->select($primaryIdentity)->fetchRow();
        }
        return $row;
    }

    public function getPrimaryIdentity($primaryVal)
    {
        return [$this->rowFactory->getPrimary() => $primaryVal];
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
            if ($this->identityMap->hasPrimary($primaryIdentity)) {
                $rows[$primaryVal] = $this->identityMap->getRowByPrimary($primaryIdentity);
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
        $colsVals = [$this->rowFactory->getPrimary() => $primaryVals];
        $select = $this->select($colsVals);
        $data = $select->cols($this->getCols())->fetchAll();
        foreach ($data as $cols) {
            $row = $this->rowFactory->newRow($cols);
            $this->identityMap->setRow($row);
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
        $this->assertRowClass($row);
        $this->rowFilter->forInsert($row);

        $insert = $this->newInsert($row);
        $writeConnection = $this->getWriteConnection();
        $pdoStatement = $writeConnection->perform(
            $insert->getStatement(),
            $insert->getBindValues()
        );

        if (! $pdoStatement->rowCount()) {
            return false;
        }

        $primary = $this->rowFactory->getPrimary();
        if ($this->getAutoinc()) {
            $row->$primary = $writeConnection->lastInsertId($primary);
        }

        // set into the identity map
        $this->identityMap->setRow($row);

        // @todo add support for "returning" into the row
        return true;
    }

    protected function newInsert(AbstractRow $row)
    {
        $cols = $this->getArrayCopyForInsert($row);

        if ($this->getAutoinc()) {
            unset($cols[$this->rowFactory->getPrimary()]);
        }

        $insert = $this->queryFactory->newInsert();
        $insert->into($this->getTable());
        $insert->cols($cols);

        return $insert;
    }

    /**
     *
     * Updates a row.
     *
     * @param Row $row The row to update.
     *
     * @return bool True if the update succeeded, false if not.  (This is
     * determined by checking the number of rows affected by the query.)
     *
     */
    public function update(AbstractRow $row)
    {
        $this->assertRowClass($row);
        $this->rowFilter->forUpdate($row);

        $update = $this->newUpdate($row);
        if (! $update) {
            return null;
        }

        $pdoStatement = $this->getWriteConnection()->perform(
            $update->getStatement(),
            $update->getBindValues()
        );

        if (! $pdoStatement->rowCount()) {
            return false;
        }

        // reinitialize the initial data for later updates
        $this->identityMap->setInitial($row);

        // @todo add support for "returning" into the row
        return true;
    }

    protected function newUpdate(AbstractRow $row)
    {
        // get the columns to update, and unset primary column
        $cols = $this->getArrayCopyForUpdate($row);
        $primaryCol = $this->rowFactory->getPrimary();
        unset($cols[$primaryCol]);

        // are there any columns to update?
        if (! $cols) {
            return;
        }

        // build the update
        $update = $this->queryFactory->newUpdate();
        $update->table($this->getTable());
        $update->cols($cols);
        $update->where("{$primaryCol} = ?", $row->getIdentity()->getVal());
        return $update;
    }

    /**
     *
     * Deletes a row through the gateway.
     *
     * @param object $row The row to delete.
     *
     * @return bool True if the delete succeeded, false if not.  (This is
     * determined by checking the number of rows affected by the query.)
     *
     */
    public function delete(AbstractRow $row)
    {
        $this->assertRowClass($row);
        $delete = $this->newDelete($row);
        $pdoStatement = $this->getWriteConnection()->perform(
            $delete->getStatement(),
            $delete->getBindValues()
        );

        return (bool) $pdoStatement->rowCount();
    }

    protected function newDelete(AbstractRow $row)
    {
        $primaryCol = $this->rowFactory->getPrimary();

        $delete = $this->queryFactory->newDelete();
        $delete->from($this->getTable());
        $delete->where("{$primaryCol} = ?", $row->getIdentity()->getVal());
        return $delete;
    }

    protected function assertRowClass(AbstractRow $row)
    {
        $rowClass = $this->rowFactory->getRowClass();
        if (! $row instanceof $rowClass) {
            $actual = get_class($row);
            throw new InvalidArgumentException("Expected {$rowClass}, got {$actual} instead");
        }
    }

    protected function getArrayCopyForInsert(AbstractRow $row)
    {
        return $row->getArrayCopy();
    }

    public function getArrayCopyForUpdate(AbstractRow $row)
    {
        $copy = $row->getArrayCopy();
        $init = $this->identityMap->getInitial($row);
        foreach ($copy as $col => $val) {
            $same = (is_numeric($copy[$col]) && is_numeric($init[$col]))
                 ? $copy[$col] == $init[$col] // numeric, compare loosely
                 : $copy[$col] === $init[$col]; // not numeric, compare strictly
            if ($same) {
                unset($copy[$col]);
            }
        }
        return $copy;
    }

}
