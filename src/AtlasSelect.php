<?php
namespace Atlas;

use Atlas\Mapper\Mapper;
use Atlas\Table\TableSelect;

/*
$atlas->select(ThreadMapper::CLASS)
    ->etc()
    ->etc()
    ->with([
        'author',
        'summary',
        'replies' => function ($select) {
            $select->limit(10)
                   ->orderBy(['datetime DESC'])
                   ->with(['author']);
        }
    ]),
    ->fetchRecordSet();

how to enable:

$atlas->fetchRecord(ThreadRecord::CLASS, 1, [
    ''
]);
*/

class AtlasSelect
{
    protected $with = [];

    public function __construct(
        Atlas $atlas,
        Mapper $mapper,
        TableSelect $tableSelect
    ) {
        $this->atlas = $atlas;
        $this->mapper = $mapper;
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

    public function fetchRecord()
    {
        $record = $this->mapper->fetchRecordBySelect($this->tableSelect);
        if ($record) {
            $this->mapper->getRelations()->stitchIntoRecord(
                $this->atlas,
                $record,
                $this->with
            );
        }
        return $record;
    }

    public function fetchRecordSet()
    {
        $recordSet = $this->mapper->fetchRecordSetBySelect($this->tableSelect);
        if ($recordSet) {
            $this->mapper->getRelations()->stitchIntoRecordSet(
                $this->atlas,
                $recordSet,
                $this->with
            );
        }
        return $recordSet;
    }
}
