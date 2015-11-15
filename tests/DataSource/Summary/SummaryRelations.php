<?php
namespace Atlas\Orm\DataSource\Summary;

use Atlas\Orm\DataSource\Thread\ThreadMapper;
use Atlas\Orm\Mapper\AbstractRelations;

class SummaryRelations extends AbstractRelations
{
    protected function setRelations()
    {
        $this->oneToOne('thread', ThreadMapper::CLASS);
    }
}
