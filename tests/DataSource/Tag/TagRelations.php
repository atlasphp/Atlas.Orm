<?php
namespace Atlas\Orm\DataSource\Tag;

use Atlas\Orm\DataSource\Tagging\TaggingMapper;
use Atlas\Orm\DataSource\Thread\ThreadMapper;
use Atlas\Orm\Mapper\AbstractRelations;

class TagRelations extends AbstractRelations
{
    protected function setRelations()
    {
        $this->oneToMany('taggings', TaggingMapper::CLASS);
        $this->manyToMany('threads', ThreadMapper::CLASS, 'taggings');
    }
}
