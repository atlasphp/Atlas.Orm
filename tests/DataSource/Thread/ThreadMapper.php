<?php
namespace Atlas\DataSource\Thread;

use Atlas\Mapper\AbstractMapper;

class ThreadMapper extends AbstractMapper
{
    public function __construct(
        ThreadTable $table,
        ThreadRecordFactory $recordFactory,
        ThreadRelations $relations
    ) {
        parent::__construct($table, $recordFactory, $relations);
    }
}
