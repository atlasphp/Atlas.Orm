<?php
namespace Atlas\DataSource\Summary;

use Atlas\DataSource\Thread\ThreadMapper;
use Atlas\Mapper\AbstractRelations;

class SummaryRelations extends AbstractRelations
{
    protected function getNativeMapperClass()
    {
        return SummaryMapper::CLASS;
    }

    protected function setRelations()
    {
        $this->hasOne('thread', ThreadMapper::CLASS);
    }
}
