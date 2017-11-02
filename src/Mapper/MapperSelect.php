<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Orm\Mapper;

use Atlas\Orm\Exception;
use Atlas\Orm\Table\TableSelect;
use Aura\SqlQuery\Common\SelectInterface;
use Aura\SqlQuery\Common\SubselectInterface;

/**
 *
 * A Select object for Mapper queries.
 *
 * @package atlas/orm
 *
 * @method array fetchAll() Fetches a sequential array of rows from the database; the rows are represented as associative arrays.
 * @method array fetchAssoc() Fetches an associative array of rows from the database; the rows are represented as associative arrays. The array of rows is keyed on the first column of each row.
 * @method array fetchCol() Fetches the first column of rows as a sequential array.
 * @method int fetchCount($col = '*') Given the existing SELECT, fetches a row count without any LIMIT or OFFSET.
 * @method array|null fetchOne() Fetches one row from the database as an associative array.
 * @method array fetchPairs() Fetches an associative array of rows as key-value pairs (first column is the key, second column is the value).
 * @method array RowInterface|null() fetchRow() Fetches a single Row object.
 * @method array fetchRows() Fetches an array of Row objects.
 * @method mixed fetchValue() Fetches the very first value (i.e., first column of the first row).
 * @method \Iterator yieldAll() Yields a sequential array of rows from the database; the rows are represented as associative arrays.
 * @method \Iterator yieldAssoc() Yields an associative array of rows from the database; the rows are represented as associative arrays. The array of rows is keyed on the first column of each row.
 * @method \Iterator yieldCol() Yields the first column of rows as a sequential array.
 * @method \Iterator yieldPairs() Yields an associative array of rows as key-value pairs (first column is the key, second column is the value).
 *
 * @method SelectInterface cols(array $cols) Adds columns to the query.
 * @method SelectInterface distinct($enable = true) Makes the select DISTINCT (or not).
 * @method SelectInterface forUpdate($enable = true) Makes the select FOR UPDATE (or not).
 * @method SelectInterface from(string $spec) Adds a FROM element to the query; quotes the table name automatically.
 * @method SelectInterface fromRaw(string $spec) Adds a raw unquoted FROM element to the query; useful for adding FROM elements that are functions.
 * @method SelectInterface fromSubSelect(string|\Aura\SqlQuery\Common\Select $spec, string $name) Adds an aliased sub-select to the query.
 * @method int getPaging() Gets the number of rows per page.
 * @method SelectInterface groupBy(array $spec) Adds grouping to the query.
 * @method SelectInterface having(string $cond) Adds a HAVING condition to the query by AND; if a value is passed as the second param, it will be quoted and replaced into the condition wherever a question-mark appears.
 * @method SelectInterface join(string $join, string $spec, string $cond = null) Adds a JOIN table and columns to the query.
 * @method SelectInterface joinSubSelect(string|\Aura\SqlQuery\Common\Select $join, string $spec, string $name, string $cond = null) Adds a JOIN to an aliased subselect and columns to the query.
 * @method SelectInterface orHaving(string $cond) Adds a HAVING condition to the query by AND; otherwise identical to `having()`.
 * @method SelectInterface page(int $page) Sets the limit and count by page number.
 * @method SelectInterface setPaging(int $paging) Sets the number of rows per page.
 * @method SelectInterface union() Takes the current select properties and retains them, then sets UNION for the next set of properties.
 * @method SelectInterface unionAll() Takes the current select properties and retains them, then sets UNION ALL for the next set of properties.
 */
class MapperSelect implements SubselectInterface
{
    /**
     *
     * The Mapper that built this Select.
     *
     * @var MapperInterface
     *
     */
    protected $mapper;

    /**
     *
     * The TableSelect being decorated.
     *
     * @var TableSelect
     *
     */
    protected $tableSelect;

    /**
     *
     * Select with these relateds.
     *
     * @var array
     *
     */
    protected $with = [];

    /**
     *
     * Constructor.
     *
     * @param MapperInterface $mapper The Mapper that created this Select.
     *
     * @param TableSelect $tableSelect The TableSelect instance being decorated.
     *
     */
    public function __construct(
        MapperInterface $mapper,
        TableSelect $tableSelect
    ) {
        $this->mapper = $mapper;
        $this->tableSelect = $tableSelect;
    }

    /**
     *
     * Decorates the underlying TableSelect object's __toString() method so that
     * (string) casting works properly.
     *
     * @return string
     *
     */
    public function __toString() : string
    {
        return $this->tableSelect->__toString();
    }

    /**
     *
     * Forwards method calls to the underlying TableSelect object.
     *
     * @param string $method The call to the underlying TableSelect object.
     *
     * @param array $params Params for the method call.
     *
     * @return mixed If the call returned the underlying TableSelect object (a
     * fluent method call) return *this* object instead to emulate the fluency;
     * otherwise return the result as-is.
     *
     */
    public function __call(string $method, array $params)
    {
        $result = call_user_func_array([$this->tableSelect, $method], $params);
        return ($result === $this->tableSelect) ? $this : $result;
    }

    /**
     *
     * Clones objects used internally.
     *
     */
    public function __clone()
    {
        $this->tableSelect = clone $this->tableSelect;
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
        return $this->tableSelect->getStatement();
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
        return $this->tableSelect->getBindValues();
    }


    /**
     *
     * Adds a JOIN clause using a named relationship from the Mapper.
     *
     * @param string $join The type of join ("LEFT", "INNER", etc.).
     *
     * @param string $relatedName The name of the relationship from the Mapper.
     *
     * @return $this
     *
     */
    public function joinWith(string $join, string $relatedName) : self
    {
        $this->mapper->getRelationships()->get($relatedName)->joinSelect($join, $this);
        return $this;
    }

    /**
     *
     * Adds a LEFT JOIN clause using a named relationship from the Mapper.
     *
     * @param string $relatedName The name of the relationship from the Mapper.
     *
     * @return $this
     *
     */
    public function leftJoinWith(string $relatedName) : self
    {
        return $this->joinWith('LEFT', $relatedName);
    }

    /**
     *
     * Adds an INNER JOIN clause using a named relationship from the Mapper.
     *
     * @param string $relatedName The name of the relationship from the Mapper.
     *
     * @return $this
     *
     */
    public function innerJoinWith(string $relatedName) : self
    {
        return $this->joinWith('INNER', $relatedName);
    }

    /**
     *
     * When fetching records, return them with these relateds.
     *
     * @param array $with Add these relateds to fetched records.
     *
     * @return $this
     *
     */
    public function with(array $with) : self
    {
        // make sure that all with() are on relateds that actually exist
        $fields = array_keys($this->mapper->getRelationships()->getFields());
        foreach ($with as $key => $val) {
            $related = $key;
            if (is_int($key)) {
                $related = $val;
            }
            if (! in_array($related, $fields)) {
                throw Exception::relationshipDoesNotExist($related);
            }
        }
        $this->with = $with;
        return $this;
    }

    /**
     *
     * Returns a Record object from the Mapper.
     *
     * @return ?RecordInterface
     *
     */
    public function fetchRecord() : ?RecordInterface
    {
        $row = $this->fetchRow();
        if (! $row) {
            return null;
        }

        return $this->mapper->turnRowIntoRecord($row, $this->with);
    }

    /**
     *
     * Returns an array of Record objects from the Mapper (*not* a RecordSet!).
     *
     * @return array
     *
     */
    public function fetchRecords() : array
    {
        $rows = $this->fetchRows();
        return $this->mapper->turnRowsIntoRecords($rows, $this->with);
    }

    /**
     *
     * Returns a RecordSet object from the Mapper.
     *
     * @return RecordSetInterface|array A RecordSet on success, or an empty
     * array on failure.
     *
     */
    public function fetchRecordSet() : RecordSetInterface
    {
        $records = $this->fetchRecords();
        return $this->mapper->newRecordSet($records);
    }
}
