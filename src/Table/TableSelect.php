<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Orm\Table;

use Aura\SqlQuery\Common\SelectInterface;
use Aura\SqlQuery\Common\SubselectInterface;
use Iterator;

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
     * The table that created this select.
     *
     * @var TableInterface
     *
     */
    protected $table;

    /**
     *
     * Constructor.
     *
     * @param TableInterface $table The table that created this select.
     *
     * @param SelectInterface $select The underlying Select object being decorated.
     *
     */
    public function __construct(
        TableInterface $table,
        SelectInterface $select
    ) {
        $this->table = $table;
        $this->select = $select;
    }

    /**
     *
     * Decorate the underlying Select object's __toString() method so that
     * (string) casting works properly.
     *
     * @return string
     *
     */
    public function __toString() : string
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
    public function __call(string $method, array $params)
    {
        $result = call_user_func_array([$this->select, $method], $params);
        return ($result === $this->select) ? $this : $result;
    }

    /**
     *
     * Clones objects used internally.
     *
     */
    public function __clone()
    {
        $this->select = clone $this->select;
    }

    /**
     *
     * Implements the SubSelect::getStatement() interface.
     *
     * @return string
     *
     */
    public function getStatement() : string
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
    public function getBindValues() : array
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
    public function fetchAll() : array
    {
        return $this->table->getReadConnection()->fetchAll(
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
    public function fetchAssoc() : array
    {
        return $this->table->getReadConnection()->fetchAssoc(
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
    public function fetchCol() : array
    {
        return $this->table->getReadConnection()->fetchCol(
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
    public function fetchOne() : ?array
    {
        $result = $this->table->getReadConnection()->fetchOne(
            $this->select->getStatement(),
            $this->select->getBindValues()
        );

        if (! $result) {
            return null;
        }

        return $result;
    }

    /**
     *
     * Fetches an associative array of rows as key-value pairs (first
     * column is the key, second column is the value).
     *
     * @return array
     *
     */
    public function fetchPairs() : array
    {
        return $this->table->getReadConnection()->fetchPairs(
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
        return $this->table->getReadConnection()->fetchValue(
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
    public function yieldAll() : Iterator
    {
        return $this->table->getReadConnection()->yieldAll(
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
    public function yieldAssoc() : Iterator
    {
        return $this->table->getReadConnection()->yieldAssoc(
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
    public function yieldCol() : Iterator
    {
        return $this->table->getReadConnection()->yieldCol(
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
    public function yieldPairs() : Iterator
    {
        return $this->table->getReadConnection()->yieldPairs(
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
    public function fetchRow() : ?RowInterface
    {
        $this->addColNames();

        $cols = $this->fetchOne();
        if (! $cols) {
            return null;
        }
        return $this->table->getSelectedRow($cols);
    }

    /**
     *
     * Fetches an array of Row objects.
     *
     * @return array
     *
     */
    public function fetchRows() : array
    {
        $this->addColNames();

        $rows = [];
        foreach ($this->yieldAll() as $cols) {
            $rows[] = $this->table->getSelectedRow($cols);
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
    public function fetchCount($col = '*') : int
    {
        $select = clone $this->select;
        $select->resetCols();
        $select->cols(["COUNT($col)"])->limit(false)->offset(false);
        return (int) $this->table->getReadConnection()->fetchValue(
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
    protected function addColNames() : void
    {
        if ($this->select->hasCols()) {
            return;
        }

        $table = $this->table->getName();
        $cols = [];
        foreach ($this->table->getColNames() as $col) {
            $cols[] = "{$table}.{$col}";
        }
        $this->select->cols($cols);
    }
}
