<?php
namespace Atlas\Fake\Summary;

use Atlas\Fake\Thread\ThreadMapper;
use Atlas\Mapper\AbstractMapper;

class SummaryMapper extends AbstractMapper
{
    protected function setMapperRelations()
    {
        $this->hasOne('thread', ThreadMapper::CLASS);
    }
}
