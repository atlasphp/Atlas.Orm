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
class Table
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

    protected $primary;

    protected $autoinc = true;

    protected $table;

    protected $cols = ['*'];

    protected $rowClass;

    protected $rowSetClass;

    protected $rowFilter;

    protected $identityMap = [];

    public function __construct(
        ConnectionLocator $connectionLocator,
        QueryFactory $queryFactory,
        IdentityMap $identityMap,
        RowFilter $rowFilter
    ) {
        $this->connectionLocator = $connectionLocator;
        $this->queryFactory = $queryFactory;
        $this->identityMap = $identityMap;
        $this->rowFilter = $rowFilter;
        $this->setDefaults();
    }

    protected function setDefaults()
    {
        // Foo\Bar\BazTable -> Foo\Bar\Baz
        $type = substr(get_class($this), 0, -5);

        // Foo\Bar\Baz => baz
        $name = strtolower(substr($type, strrpos($type, '\\') + 1));

        if (! $this->table) {
            $this->table = $name;
        }

        if (! $this->primary) {
            $this->primary = "{$name}_id";
        }

        $this->autoinc = (bool) $this->autoinc;

        $this->rowClass = "{$type}Row";
        if (! class_exists($this->rowClass)) {
            throw new Exception("{$this->rowClass} does not exist");
        }

        $this->rowSetClass = "{$type}RowSet";
        if (! class_exists($this->rowSetClass)) {
            throw new Exception("{$this->rowSetClass} does not exist");
        }
    }

    public function getTable()
    {
        return $this->table;
    }

    /**
     *
     * Returns the primary column name on the table.
     *
     * @return string The primary column name.
     *
     */
    public function getPrimary()
    {
        return $this->primary;
    }

    /**
     *
     * Does the database set the primary key value on insert by autoincrement?
     *
     * @return bool
     *
     */
    public function getAutoinc()
    {
        return $this->autoinc;
    }

    public function getRowClass()
    {
        return $this->rowClass;
    }

    public function getRowSetClass()
    {
        return $this->rowSetClass;
    }

    /**
     *
     * Select these columns.
     *
     * @return array
     *
     */
    public function getCols()
    {
        return (array) $this->cols;
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
        $row = $this->identityMap->getRow($primaryVal);
        if (! $row) {
            $colsVals = [$this->getPrimary() => $primaryVal];
            $row = $this->select($colsVals)->fetchRow();
        }
        return $row;
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

        return $this->newRowSet(array_values($rows));
    }

    // get existing rows from identity map
    protected function fillExistingRows(&$primaryVals, &$rows)
    {
        foreach ($primaryVals as $i => $primaryVal) {
            $rows[$primaryVal] = null;
            if ($this->identityMap->hasPrimaryVal($primaryVal)) {
                $rows[$primaryVal] = $this->identityMap->getRow($primaryVal);
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
        $colsVals = [$this->getPrimary() => $primaryVals];
        $select = $this->select($colsVals);
        $data = $select->cols($this->getCols())->fetchAll();
        foreach ($data as $cols) {
            $row = $this->newRow($cols);
            $this->identityMap->set($row);
            $rows[$row->getPrimaryVal()] = $row;
        }
    }

    public function fetchRowSetBy(array $colsVals)
    {
        return $this->select($colsVals)->fetchRowSet();
    }

    public function newRow(array $cols)
    {
        $rowClass = $this->getRowClass();
        return new $rowClass($cols, $this->getPrimary());
    }

    public function newRowSet(array $rows)
    {
        $rowSetClass = $this->getRowSetClass();
        return new $rowSetClass($rows, $this->rowClass);
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
    public function insert(Row $row)
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

        $primary = $this->getPrimary();
        if ($this->getAutoinc()) {
            $row->$primary = $writeConnection->lastInsertId($primary);
        }

        // set into the identity map
        $this->identityMap->set($row);

        // reinitialize the initial data for later updates
        $row->init();

        // @todo add support for "returning" into the row
        return true;
    }

    protected function newInsert(Row $row)
    {
        $cols = $row->getArrayCopyForInsert();

        if ($this->getAutoinc()) {
            unset($cols[$this->getPrimary()]);
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
    public function update(Row $row)
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
        $row->init();

        // @todo add support for "returning" into the row
        return true;
    }

    protected function newUpdate(Row $row)
    {
        // get the columns to update, and unset primary column
        $cols = $row->getArrayCopyForUpdate();
        $primaryCol = $this->getPrimary();
        unset($cols[$primaryCol]);

        // are there any columns to update?
        if (! $cols) {
            return;
        }

        // build the update
        $update = $this->queryFactory->newUpdate();
        $update->table($this->getTable());
        $update->cols($cols);
        $update->where("{$primaryCol} = ?", $row->getPrimaryVal());
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
    public function delete(Row $row)
    {
        $this->assertRowClass($row);
        $delete = $this->newDelete($row);
        $pdoStatement = $this->getWriteConnection()->perform(
            $delete->getStatement(),
            $delete->getBindValues()
        );

        return (bool) $pdoStatement->rowCount();
    }

    protected function newDelete(Row $row)
    {
        $primaryCol = $this->getPrimary();

        $delete = $this->queryFactory->newDelete();
        $delete->from($this->getTable());
        $delete->where("{$primaryCol} = ?", $row->getPrimaryVal());
        return $delete;
    }

    protected function assertRowClass(Row $row)
    {
        if (! $row instanceof $this->rowClass) {
            $actual = get_class($row);
            throw new InvalidArgumentException("Expected {$this->rowClass}, got {$actual} instead");
        }
    }
}
