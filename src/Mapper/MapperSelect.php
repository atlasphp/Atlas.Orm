<?php
namespace Atlas\Orm\Mapper;

use Atlas\Orm\Table\TableSelect;
use Aura\Sql\ExtendedPdo;
use Aura\SqlQuery\Common\SelectInterface;
use Aura\SqlQuery\Common\SubselectInterface;

class MapperSelect implements SubselectInterface
{
    /**
     *
     * The TableSelect being decorated.
     *
     * @var TableSelect
     *
     */
    protected $tableSelect;

    protected $getSelectedRecord;

    protected $getSelectedRecordSet;

    protected $with = [];

    public function __construct(
        TableSelect $tableSelect,
        callable $getSelectedRecord,
        callable $getSelectedRecordSet
    ) {
        $this->tableSelect = $tableSelect;
        $this->getSelectedRecord = $getSelectedRecord;
        $this->getSelectedRecordSet = $getSelectedRecordSet;
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
        return $this->tableSelect->__toString();
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
        $result = call_user_func_array([$this->tableSelect, $method], $params);
        return ($result === $this->tableSelect) ? $this : $result;
    }

    // subselect interface
    public function getStatement()
    {
        return $this->tableSelect->getStatement();
    }

    // subselect interface
    public function getBindValues()
    {
        return $this->tableSelect->getBindValues();
    }

    public function with(array $with)
    {
        $this->with = $with;
        return $this;
    }

    public function fetchRecord()
    {
        $this->tableSelect->cols($this->tableSelect->getColNames());
        $cols = $this->fetchOne();
        if (! $cols) {
            return false;
        }

        return call_user_func($this->getSelectedRecord, $cols, $this->with);
    }

    public function fetchRecordSet()
    {
        $this->tableSelect->cols($this->tableSelect->getColNames());

        $data = $this->fetchAll();
        if (! $data) {
            return [];
        }

        return call_user_func($this->getSelectedRecordSet, $data, $this->with);
    }

    public function fetchRecordsArray()
    {
        $this->tableSelect->cols($this->tableSelect->getColNames());

        $records = [];
        $data = $this->fetchAll();
        foreach ($data as $cols) {
            $records[] = call_user_func($this->getSelectedRecord, $cols, $this->with);
        }
        return $records;
    }
}
