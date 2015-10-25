<?php
namespace Atlas\DataSource\Reply;

use Atlas\Mapper\AbstractMapper;

class ReplyMapper extends AbstractMapper
{
    public function __construct(
        ReplyTable $table,
        ReplyRecordFactory $recordFactory,
        ReplyRelations $relations
    ) {
        parent::__construct($table, $recordFactory, $relations);
    }
}
