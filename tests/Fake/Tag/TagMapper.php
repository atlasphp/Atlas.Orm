<?php
namespace Atlas\Fake\Tag;

use Atlas\Fake\Tagging\TaggingMapper;
use Atlas\Fake\Thread\ThreadMapper;
use Atlas\Mapper\AbstractMapper;

class TagMapper extends AbstractMapper
{
    protected function setMapperRelations()
    {
        $this->hasMany('taggings', TaggingMapper::CLASS);
        $this->hasManyThrough('threads', ThreadMapper::CLASS, 'taggings');
    }
}
