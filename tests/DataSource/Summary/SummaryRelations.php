<?php
namespace Atlas\DataSource\Summary;

use Atlas\DataSource\Thread\ThreadMapper;
use Atlas\Mapper\AbstractRelations;

class SummaryRelations extends AbstractRelations
{
    protected function setRelations()
    {
        $this->oneToOne('thread', ThreadMapper::CLASS);
    }
}
