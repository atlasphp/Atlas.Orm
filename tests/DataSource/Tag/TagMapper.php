<?php
namespace Atlas\DataSource\Tag;

use Atlas\Mapper\AbstractMapper;

class TagMapper extends AbstractMapper
{
    public function __construct(
        TagTable $table,
        TagRecordFactory $recordFactory,
        TagRecordFilter $recordFilter,
        TagRelations $relations
    ) {
        parent::__construct(
            $table,
            $recordFactory,
            $recordFilter,
            $relations
        );
    }
}
