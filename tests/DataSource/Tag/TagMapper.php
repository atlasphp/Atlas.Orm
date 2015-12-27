<?php
namespace Atlas\Orm\DataSource\Tag;

use Atlas\Orm\DataSource\Tagging\TaggingMapper;
use Atlas\Orm\DataSource\Thread\ThreadMapper;
use Atlas\Orm\Mapper\Mapper;

class TagMapper extends Mapper
{
    protected function setRelated()
    {
        $this->oneToMany('taggings', TaggingMapper::CLASS);
        $this->manyToMany('threads', ThreadMapper::CLASS, 'taggings');
    }
}
