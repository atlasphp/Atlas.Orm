<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Orm\Table;

use Aura\Sql\ExtendedPdo;
use Aura\SqlQuery\Common\SelectInterface;
use Aura\SqlQuery\Common\SubselectInterface;

/**
 *
 * A Select object for Table queries.
 *
 * @package atlas/orm
 *
 */
class TableSelect implements SubselectInterface
{
    /**
     *
     * The underlying Select object being decorated.
     *
     * @var SelectInterface
     *
     */
    protected $select;

    /**
     *
     * A database read connection.
     *
     * @var ExtendedPdo
     *
     */
    protected $connection;

    /**
     *
     * The column names in the Table.
     *
     * @var array
     *
     */
    protected $colNames;

    /**
     *
     * A callable to create a Row from the select results.
     *
     * @var callable
     *
     */
    protected $getSelectedRow;

    /**
     *
     * Constructor.
     *
     * @param SelectInterface The underlying Select object being decorated.
     *
     * @param ExtendedPdo $connection A database read connection.
     *
     * @param array $colNames The column names in the Table.
     *
     * @param callable $getSelectedRow A callable to create a Row from the
     * select results.
     *
     */
    public function __construct(
        SelectInterface $select,
        ExtendedPdo $connection,
        array $colNames,
        callable $getSelectedRow
    ) {
        $this->select = $select;
        $this->connection = $connection;
        $this->colNames = $colNames;
        $this->getSelectedRow = $getSelectedRow;
    }

    /**
     *
     * Decorate the underlying Select object's __toString() method so that
     * (string) casting works properly.
     *
     * @return string
     *
     */
    public function __toString()
    {
        $this->addColNames();
        return $this->select->__toString();
    }

    /**
     *
     * Forwards method calls to the underlying Select object.
     *
     * @param string $method The call to the underlying Select object.
     *
     * @param array $params Params for the method call.
     *
     * @return mixed If the call returned the underlying Select object (a fluent
     * method call) return *this* object instead to emulate the fluency;
     * otherwise return the result as-is.
     *
     */
    public function __call($method, $params)
    {
        $result = call_user_func_array([$this->select, $method], $params);
        return ($result === $this->select) ? $this : $result;
    }

    /**
     *
     * Implements the SubSelect::getStatement() interface.
     *
     * @return string
     *
     */
    public function getStatement()
    {
        $this->addColNames();
        return $this->select->getStatement();
    }

    /**
     *
     * Implements the SubSelect::getBindValues() interface.
     *
     * @return array
     *
     */
    public function getBindValues()
    {
        return $this->select->getBindValues();
    }

    /**
     *
     * Fetches a sequential array of rows from the database; the rows
     * are represented as associative arrays.
     *
     * @return array
     *
     */
    public function fetchAll()
    {
        return $this->connection->fetchAll(
            $this->select->getStatement(),
            $this->select->getBindValues()
        );
    }

    /**
     *
     * Fetches an associative array of rows from the database; the rows
     * are represented as associative arrays. The array of rows is keyed
     * on the first column of each row.
     *
     * N.b.: if multiple rows have the same first column value, the last
     * row with that value will override earlier rows.
     *
     * @return array
     *
     */
    public function fetchAssoc()
    {
        return $this->connection->fetchAssoc(
            $this->select->getStatement(),
            $this->select->getBindValues()
        );
    }

    /**
     *
     * Fetches the first column of rows as a sequential array.
     *
     * @return array
     *
     */
    public function fetchCol()
    {
        return $this->connection->fetchCol(
            $this->select->getStatement(),
            $this->select->getBindValues()
        );
    }

    /**
     *
     * Fetches one row from the database as an associative array.
     *
     * @return array
     *
     */
    public function fetchOne()
    {
        return $this->connection->fetchOne(
            $this->select->getStatement(),
            $this->select->getBindValues()
        );
    }

    /**
     *
     * Fetches an associative array of rows as key-value pairs (first
     * column is the key, second column is the value).
     *
     * @param array $values Values to bind to the query.
     *
     * @return array
     *
     */
    public function fetchPairs()
    {
        return $this->connection->fetchPairs(
            $this->select->getStatement(),
            $this->select->getBindValues()
        );
    }

    /**
     *
     * Fetches the very first value (i.e., first column of the first row).
     *
     * @return mixed
     *
     */
    public function fetchValue()
    {
        return $this->connection->fetchValue(
            $this->select->getStatement(),
            $this->select->getBindValues()
        );
    }

    /**
     *
     * Yields a sequential array of rows from the database; the rows
     * are represented as associative arrays.
     *
     * @return Iterator
     *
     */
    public function yieldAll()
    {
        return $this->connection->yieldAll(
            $this->select->getStatement(),
            $this->select->getBindValues()
        );
    }

    /**
     *
     * Yields an associative array of rows from the database; the rows
     * are represented as associative arrays. The array of rows is keyed
     * on the first column of each row.
     *
     * N.b.: if multiple rows have the same first column value, the last
     * row with that value will override earlier rows.
     *
     * @return Iterator
     *
     */
    public function yieldAssoc()
    {
        return $this->connection->yieldAssoc(
            $this->select->getStatement(),
            $this->select->getBindValues()
        );
    }

    /**
     *
     * Yields the first column of rows as a sequential array.
     *
     * @return Iterator
     *
     */
    public function yieldCol()
    {
        return $this->connection->yieldCol(
            $this->select->getStatement(),
            $this->select->getBindValues()
        );
    }

    /**
     *
     * Yields an associative array of rows as key-value pairs (first
     * column is the key, second column is the value).
     *
     * @param array $values Values to bind to the query.
     *
     * @return Iterator
     *
     */
    public function yieldPairs()
    {
        return $this->connection->yieldPairs(
            $this->select->getStatement(),
            $this->select->getBindValues()
        );
    }

    /**
     *
     * Fetches a single Row object.
     *
     * @return RowInterface|false A Row on success, or false on failure.
     *
     */
    public function fetchRow()
    {
        $this->addColNames();

        $cols = $this->fetchOne();
        if (! $cols) {
            return false;
        }
        return call_user_func($this->getSelectedRow, $cols);
    }

    /**
     *
     * Fetches an array of Row objects.
     *
     * @return array
     *
     */
    public function fetchRows()
    {
        $this->addColNames();

        $rows = [];
        foreach ($this->yieldAll() as $cols) {
            $rows[] = call_user_func($this->getSelectedRow, $cols);
        }
        return $rows;
    }

    /**
     *
     * Given the existing SELECT, fetches a row count without any LIMIT or
     * OFFSET.
     *
     * @param string $col Count on this column.
     *
     * @return int
     *
     */
    public function fetchCount($col = '*')
    {
        $select = clone $this->select;
        $select->resetCols();
        $select->cols(["COUNT($col)"])->limit(false)->offset(false);
        return (int) $this->connection->fetchValue(
            $select->getStatement(),
            $select->getBindValues()
        );
    }

    /**
     *
     * Adds all table columns to the SELECT if it has no columns yet.
     *
     * @return void
     *
     */
    protected function addColNames()
    {
        if (! $this->select->hasCols()) {
            $this->select->cols($this->colNames);
        }
    }
}
