<?php
namespace Atlas\DataSource\Tag;

use Atlas\DataSource\Tagging\TaggingMapper;
use Atlas\DataSource\Thread\ThreadMapper;
use Atlas\Mapper\AbstractMapper;

class TagMapper extends AbstractMapper
{
    protected function setMapperRelations()
    {
        $this->hasMany('taggings', TaggingMapper::CLASS);
        $this->hasManyThrough('threads', ThreadMapper::CLASS, 'taggings');
    }
}
