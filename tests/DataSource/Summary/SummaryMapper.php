<?php
namespace Atlas\Orm\DataSource\Summary;

use Atlas\Orm\DataSource\Thread\ThreadMapper;
use Atlas\Orm\Mapper\Mapper;

class SummaryMapper extends Mapper
{
    protected function defineRelationship()
    {
        $this->oneToOne('thread', ThreadMapper::CLASS);
    }
}
