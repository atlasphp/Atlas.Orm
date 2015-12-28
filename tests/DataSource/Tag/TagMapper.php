<?php
namespace Atlas\Orm\DataSource\Tag;

use Atlas\Orm\DataSource\Tagging\TaggingMapper;
use Atlas\Orm\DataSource\Thread\ThreadMapper;
use Atlas\Orm\Mapper\AbstractMapper;

class TagMapper extends AbstractMapper
{
    protected function setRelated()
    {
        $this->oneToMany('taggings', TaggingMapper::CLASS);
        $this->manyToMany('threads', ThreadMapper::CLASS, 'taggings');
    }
}
