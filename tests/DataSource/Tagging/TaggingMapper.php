<?php
namespace Atlas\Orm\DataSource\Tagging;

use Atlas\Orm\Mapper\AbstractMapper;
use Atlas\Orm\DataSource\Thread\ThreadMapper;
use Atlas\Orm\DataSource\Tag\TagMapper;

class TaggingMapper extends AbstractMapper
{
    protected function setRelated()
    {
        $this->manyToOne('thread', ThreadMapper::CLASS);
        $this->manyToOne('tag', TagMapper::CLASS);
    }
}
