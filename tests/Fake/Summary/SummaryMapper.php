<?php
namespace Atlas\Fake\Summary;

use Atlas\Fake\Thread\ThreadMapper;
use Atlas\Mapper\Mapper;

class SummaryMapper extends Mapper
{
    protected function setRelations()
    {
        $this->relations->oneToOne('thread', ThreadMapper::CLASS);
    }
}
