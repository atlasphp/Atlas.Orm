<?php
namespace Atlas\Orm\DataSource\Summary;

use Atlas\Orm\DataSource\Thread\ThreadMapper;
use Atlas\Orm\Mapper\AbstractMapper;

class SummaryMapper extends AbstractMapper
{
    protected function setRelated()
    {
        $this->oneToOne('thread', ThreadMapper::CLASS);
    }
}
