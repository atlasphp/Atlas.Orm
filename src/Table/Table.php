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

use Aura\Sql\ConnectionLocator;
use Aura\SqlQuery\QueryFactory;
use Aura\SqlQuery\Common\Delete;
use Aura\SqlQuery\Common\Insert;
use Aura\SqlQuery\Common\Update;

/**
 *
 * A Gateway+Mapper that returns Row and RowSet objects.
 *
 * @todo Extract row/rowset factory methods to a RowFactory object? Maybe not;
 * they are supposed to be stupid bags of data.
 *
 * @todo Allow a Filter to apply against Row objects.
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

    protected $cols = [];

    protected $rowClass;

    protected $rowsetClass;

    protected $identityMap = [];

    public function __construct(
        ConnectionLocator $connectionLocator,
        QueryFactory $queryFactory,
        IdentityMap $identityMap
    ) {
        $this->connectionLocator = $connectionLocator;
        $this->queryFactory = $queryFactory;
        $this->identityMap = $identityMap;
    }

    public function getTable()
    {
        if (! $this->table) {
            // Foo\Bar\BazTable -> baz
            $class = get_class($this);
            $pos = strrpos($class, '\\') + 1;
            $this->table = strtolower(substr($class, $pos, -5));
        }
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
        if (! $this->primary) {
            // Foo\Bar\BazTable -> baz_id
            $class = get_class($this);
            $pos = strrpos($class, '\\') + 1;
            $this->primary = strtolower(substr($class, $pos, -5)) . '_id';
        }
        return $this->primary;
    }

    protected function getPrimaryVal($cols)
    {
        return $cols[$this->getPrimary()];
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

    /**
     *
     * Select these columns.
     *
     * @return array
     *
     */
    protected function getCols()
    {
        if (! $this->cols) {
            $this->cols = ['*'];
        }
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
     * @return Select
     *
     */
    public function select(array $colsVals = [])
    {
        $select = $this->newSelect()->from($this->getTable());

        if (! $colsVals) {
            return $select;
        }

        foreach ($colsVals as $col => $val) {
            $this->selectWhere($select, $col, $val);
        }

        return $select;
    }

    protected function newSelect()
    {
        return new Select(
            $this->queryFactory->newSelect(),
            $this->getReadConnection()
        );
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
            $row = $this->fetchRowBy($colsVals);
        }
        return $row;
    }

    public function fetchRowBy(array $colsVals)
    {
        $select = $this->select($colsVals);
        return $this->fetchRowBySelect($select);
    }

    public function fetchRowBySelect(Select $select)
    {
        $select->cols($this->getCols());

        $cols = $select->fetchOne();
        if (! $cols) {
            return false;
        }

        return $this->getMappedOrNewRow($cols);
    }

    /*
    RowSet by primary:
        create empty set
        foreach primary value ...
            add null in set keyed on primary value to maintain place
            if primary value in map
                retain mapped row in set keyed on primary value
                remove primary value from list
        select remaining primary values
        foreach returned one ...
            new row object
            retain row in map
            add row in set on ID key
        return new RowSet from array set
    */
    public function fetchRowSet(array $primaryVals)
    {
        $rows = [];

        // pre-empt working on empty array
        if (! $primaryVals) {
            return array();
        }

        // get existing rows from identity map
        foreach ($primaryVals as $i => $primaryVal) {
            $rows[$primaryVal] = null;
            if ($this->identityMap->hasPrimaryVal($primaryVal)) {
                $rows[$primaryVal] = $this->identityMap->getRow($primaryVal);
                unset($primaryVals[$i]);
            }
        }

        // if there are no rows to fetch, we're done
        if (! $primaryVals) {
            return $this->newRowSet(array_values($rows));
        }

        // fetch and retain remaining rows
        $colsVals = [$this->getPrimary() => $primaryVals];
        $data = $this->select($colsVals)->cols($this->getCols())->fetchAll();
        foreach ($data as $cols) {
            $primaryVal = $this->getPrimaryVal($cols);
            $row = $this->newRow($cols);
            $this->identityMap->set($row);
            $rows[$primaryVal] = $row;
        }

        // remove unfound rows
        foreach ($rows as $key => $val) {
            if ($val === null) {
                unset($rows[$key]);
            }
        }

        // did we find anything?
        if (! $rows) {
            return array();
        }

        // done!
        return $this->newRowSet(array_values($rows));
    }

    public function fetchRowSetBy(array $colsVals, callable $custom = null)
    {
        $select = $this->select($colsVals);
        if ($custom) {
            $custom($select);
        }
        return $this->fetchRowSetBySelect($select);
    }

    /*
    RowSet by arbitrary:
        select by arbitrary
        create empty set
        foreach row in data ...
            if ID in map, retain mapped row in set
            else
                new row object
                retain row in map
                add row in set on ID key
        return new RowSet from array set
    */
    public function fetchRowSetBySelect(Select $select)
    {
        $data = $select->cols($this->getCols())->fetchAll();
        if (! $data) {
            return array();
        }

        $rows = [];
        foreach ($data as $cols) {
            $rows[] = $this->getMappedOrNewRow($cols);
        }

        return $this->newRowSet($rows);
    }

    protected function getMappedOrNewRow(array $cols)
    {
        $primaryVal = $this->getPrimaryVal($cols);
        $row = $this->identityMap->getRow($primaryVal);
        if (! $row) {
            $row = $this->newRow($cols);
            $this->identityMap->set($row);
        }
        return $row;
    }

    public function newRow(array $cols)
    {
        $rowClass = $this->getRowClass();
        return new $rowClass($cols, $this->getPrimary());
    }

    public function getRowClass()
    {
        if (! $this->rowClass) {
            // Foo\Bar\BazTable -> Foo\Bar\BazRow
            $class = substr(get_class($this), -5);
            $this->rowClass = "{$class}Row";
        }

        if (! class_exists($this->rowClass)) {
            $this->rowClass = 'Atlas\Table\Row';
        }

        return $this->rowClass;
    }

    public function newRowSet(array $rows)
    {
        $rowsetClass = $this->getRowSetClass();
        return new $rowsetClass($rows);
    }

    public function getRowSetClass()
    {
        if (! $this->rowsetClass) {
            // Foo\Bar\BazTable -> Foo\Bar\BazRowSet
            $class = substr(get_class($this), -5);
            $this->rowsetClass = "{$class}RowSet";
        }

        if (! class_exists($this->rowsetClass)) {
            $this->rowsetClass = 'Atlas\Table\RowSet';
        }

        return $this->rowsetClass;
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
}
