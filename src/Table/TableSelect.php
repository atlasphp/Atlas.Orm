<?php
/**
 *
 * This file is part of the Aura Project for PHP.
 *
 * @package Atlas.Atlas
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Table;

use Aura\Sql\ExtendedPdo;
use Aura\SqlQuery\Common\SelectInterface;
use Aura\SqlQuery\Common\SubselectInterface;

/**
 *
 * A Select that supports direct fetching.
 *
 * @package Atlas.Atlas
 *
 */
class TableSelect implements SubselectInterface
{
    /**
     *
     * The SelectInterface being decorated.
     *
     * @var mixed
     *
     */
    protected $select;

    protected $table;

    protected $identityMap;

    protected $primaryCol;

    /**
     *
     * @param SelectInterface $select
     *
     */
    public function __construct(
        Table $table,
        SelectInterface $select
    ) {
        $this->table = $table;
        $this->select = $select;
        $this->identityMap = $this->table->getIdentityMap();
        $this->primaryCol = $this->table->getPrimary();
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
        return $this->select->getStatement();
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

    // subselect interface
    public function getStatement()
    {
        return $this->select->getStatement();
    }

    // subselect interface
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
    public function fetchAssoc()
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
    public function fetchCol()
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
    public function fetchOne()
    {
        return $this->table->getReadConnection()->fetchOne(
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

    public function fetchRow()
    {
        $this->select->cols($this->table->getCols());

        $cols = $this->fetchOne();
        if (! $cols) {
            return false;
        }

        return $this->getMappedOrNewRow($cols);
    }

    public function fetchRowSet()
    {
        $this->select->cols($this->table->getCols());

        $data = $this->fetchAll();
        if (! $data) {
            return array();
        }

        $rows = [];
        foreach ($data as $cols) {
            $rows[] = $this->getMappedOrNewRow($cols);
        }

        return $this->table->newRowSet($rows);
    }

    protected function getMappedOrNewRow(array $cols)
    {
        $primaryVal = $cols[$this->primaryCol];
        $row = $this->identityMap->getRow($primaryVal);
        if (! $row) {
            $row = $this->table->newRow($cols);
            $this->identityMap->set($row);
        }
        return $row;
    }
}
