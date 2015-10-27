<?php
namespace Atlas\DataSource\Reply;

use Atlas\Mapper\AbstractMapper;

class ReplyMapper extends AbstractMapper
{
    public function __construct(
        ReplyTable $table,
        ReplyRecordFactory $recordFactory,
        ReplyRecordFilter $recordFilter,
        ReplyRelations $relations
    ) {
        parent::__construct(
            $table,
            $recordFactory,
            $recordFilter,
            $relations
        );
    }
}
