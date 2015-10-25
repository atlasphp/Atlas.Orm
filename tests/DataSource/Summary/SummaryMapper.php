<?php
namespace Atlas\DataSource\Summary;

use Atlas\Mapper\AbstractMapper;

class SummaryMapper extends AbstractMapper
{
    public function __construct(
        SummaryTable $table,
        SummaryRecordFactory $recordFactory,
        SummaryRelations $relations
    ) {
        parent::__construct($table, $recordFactory, $relations);
    }
}
