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
use Aura\Sql\ExtendedPdo;
use Aura\SqlQuery\Common\SelectInterface;
use Aura\SqlQuery\Common\SubselectInterface;

/**
 *
 * A Select object for Mapper queries.
 *
 * @package atlas/orm
 *
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
     * @param Mapper $mapper The Mapper that created this Select.
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
    public function __toString()
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
    public function __call($method, $params)
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
    public function getStatement()
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
    public function getBindValues()
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
    public function joinWith($join, $relatedName)
    {
        $nativeTable = $this->mapper->getTable()->getName();
        $relationship = $this->mapper->getRelationships()->get($relatedName);
        $foreignTable = $relationship->getForeignMapper()->getTable()->getName();
        $spec = "{$foreignTable} AS {$relatedName}";

        $cond = [];
        foreach ($relationship->getOn() as $nativeCol => $foreignCol) {
            $cond[] = "{$nativeTable}.{$nativeCol} = {$relatedName}.{$foreignCol}";
        }
        $cond = implode(' AND ', $cond);

        return $this->join($join, $spec, $cond);
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
    public function leftJoinWith($relatedName)
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
    public function innerJoinWith($relatedName)
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
    public function with(array $with)
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
     * @return RecordInterface|false A Record on success, or false on failure.
     *
     */
    public function fetchRecord()
    {
        $row = $this->fetchRow();
        if (! $row) {
            return false;
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
    public function fetchRecords()
    {
        $rows = $this->fetchRows();
        if (! $rows) {
            return [];
        }

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
    public function fetchRecordSet()
    {
        $rows = $this->fetchRows();
        if (! $rows) {
            return [];
        }

        return $this->mapper->turnRowsIntoRecordSet($rows, $this->with);
    }
}
