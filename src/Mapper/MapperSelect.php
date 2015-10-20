<?php
namespace Atlas\Mapper;

use Atlas\Table\TableSelect;

class MapperSelect
{
    protected $with = [];

    protected $tableSelect;

    public function __construct(AbstractMapper $mapper, TableSelect $tableSelect)
    {
        $this->mapper = $mapper;
        $this->tableSelect = $tableSelect;
    }

    public function __call($method, $params)
    {
        $result = call_user_func_array([$this->tableSelect, $method], $params);
        return ($result === $this->tableSelect) ? $this : $result;
    }

    public function with(array $with)
    {
        $this->with = $with;
        return $this;
    }

    public function getTableSelect()
    {
        return $this->tableSelect;
    }

    public function fetchRecord()
    {
        $row = $this->tableSelect->fetchRow();
        return $this->mapper->convertRow($row, $this->with);
    }

    public function fetchRecordSet()
    {
        $rowSet = $this->tableSelect->fetchRowSet();
        return $this->mapper->convertRowSet($rowSet, $this->with);
    }
}
