<?php
namespace Atlas\Mapper;

use Atlas\Mapper\Mapper;
use Atlas\Table\TableSelect;

class MapperSelect
{
    protected $with = [];

    protected $tableSelect;

    public function __construct(TableSelect $tableSelect)
    {
        $this->tableSelect = $tableSelect;
    }

    public function __call($method, $params)
    {
        $result = call_user_func_array(
            [$this->tableSelect, $method],
            $params
        );
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

    public function getWith()
    {
        return $this->with;
    }
}
