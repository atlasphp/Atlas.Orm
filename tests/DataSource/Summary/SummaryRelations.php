<?php
namespace Atlas\Orm\DataSource\Summary;

use Atlas\Orm\DataSource\Thread\ThreadMapper;
use Atlas\Orm\Mapper\MapperRelations;

class SummaryRelations extends MapperRelations
{
    protected function setRelations()
    {
        $this->oneToOne('thread', ThreadMapper::CLASS);
    }
}
