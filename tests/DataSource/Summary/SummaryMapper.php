<?php
namespace Atlas\DataSource\Summary;

use Atlas\DataSource\Thread\ThreadMapper;
use Atlas\Mapper\AbstractMapper;

class SummaryMapper extends AbstractMapper
{
    protected function setMapperRelations()
    {
        $this->hasOne('thread', ThreadMapper::CLASS);
    }
}
