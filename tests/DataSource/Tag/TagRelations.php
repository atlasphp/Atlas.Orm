<?php
namespace Atlas\DataSource\Tag;

use Atlas\DataSource\Tagging\TaggingMapper;
use Atlas\DataSource\Thread\ThreadMapper;
use Atlas\Mapper\AbstractRelations;

class TagRelations extends AbstractRelations
{
    protected function setRelations()
    {
        $this->hasMany('taggings', TaggingMapper::CLASS);
        $this->hasManyThrough('threads', ThreadMapper::CLASS, 'taggings');
    }
}
