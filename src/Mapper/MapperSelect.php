<?php
namespace Atlas\Orm\Mapper;

use Atlas\Orm\Table\TableSelect;

class MapperSelect
{
    protected $with = [];

    protected $tableSelect;

    public function __construct(
        TableSelect $tableSelect,
        callable $newRecordFromRow,
        callable $newRecordSetFromRowSet
    ) {
        $this->tableSelect = $tableSelect;
        $this->newRecordFromRow = $newRecordFromRow;
        $this->newRecordSetFromRowSet = $newRecordSetFromRowSet;
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
        if (! $row) {
            return false;
        }
        return call_user_func($this->newRecordFromRow, $row, $this->with);
    }

    public function fetchRecordSet()
    {
        $rowSet = $this->tableSelect->fetchRowSet();
        if (! $rowSet) {
            return array();
        }
        return call_user_func($this->newRecordSetFromRowSet, $rowSet, $this->with);
    }
}
