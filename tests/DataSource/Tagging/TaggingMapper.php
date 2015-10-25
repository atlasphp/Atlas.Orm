<?php
namespace Atlas\DataSource\Tagging;

use Atlas\Mapper\AbstractMapper;

class TaggingMapper extends AbstractMapper
{
    public function __construct(
        TaggingTable $table,
        TaggingRecordFactory $recordFactory,
        TaggingRelations $relations
    ) {
        parent::__construct($table, $recordFactory, $relations);
    }
}
