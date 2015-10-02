<?php
namespace Atlas\Fake\Tag;

use Atlas\Fake\Tagging\TaggingMapper;
use Atlas\Fake\Thread\ThreadMapper;
use Atlas\Mapper\Mapper;

class TagMapper extends Mapper
{
    protected function setMapperRelations()
    {
        $this->hasMany('taggings', TaggingMapper::CLASS);
        $this->hasManyThrough('threads', ThreadMapper::CLASS, 'taggings');
    }
}
