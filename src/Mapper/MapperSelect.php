<?php
namespace Atlas\Orm\Mapper;

use Atlas\Orm\Table\GatewaySelect;
use Aura\Sql\ExtendedPdo;
use Aura\SqlQuery\Common\SelectInterface;
use Aura\SqlQuery\Common\SubselectInterface;

class MapperSelect implements SubselectInterface
{
    /**
     *
     * The GatewaySelect being decorated.
     *
     * @var GatewaySelect
     *
     */
    protected $gatewaySelect;

    protected $getSelectedRecord;

    protected $getSelectedRecordSet;

    protected $with = [];

    public function __construct(
        GatewaySelect $gatewaySelect,
        callable $getSelectedRecord,
        callable $getSelectedRecordSet
    ) {
        $this->gatewaySelect = $gatewaySelect;
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
        return $this->gatewaySelect->getStatement();
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
        $result = call_user_func_array([$this->gatewaySelect, $method], $params);
        return ($result === $this->gatewaySelect) ? $this : $result;
    }

    // subselect interface
    public function getStatement()
    {
        return $this->gatewaySelect->getStatement();
    }

    // subselect interface
    public function getBindValues()
    {
        return $this->gatewaySelect->getBindValues();
    }

    public function with(array $with)
    {
        $this->with = $with;
        return $this;
    }

    public function fetchRecord()
    {
        $this->gatewaySelect->cols($this->gatewaySelect->getColNames());
        $cols = $this->fetchOne();
        if (! $cols) {
            return false;
        }

        return call_user_func($this->getSelectedRecord, $cols, $this->with);
    }

    public function fetchRecordSet()
    {
        $this->gatewaySelect->cols($this->gatewaySelect->getColNames());

        $data = $this->fetchAll();
        if (! $data) {
            return [];
        }

        return call_user_func($this->getSelectedRecordSet, $data, $this->with);
    }

    public function fetchRecordsArray()
    {
        $this->gatewaySelect->cols($this->gatewaySelect->getColNames());

        $records = [];
        $data = $this->fetchAll();
        foreach ($data as $cols) {
            $records[] = call_user_func($this->getSelectedRecord, $cols, $this->with);
        }
        return $records;
    }
}
