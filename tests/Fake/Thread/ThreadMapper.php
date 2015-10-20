<?php
namespace Atlas\Fake\Thread;

use Atlas\Mapper\AbstractMapper;
use Atlas\Fake\Author\AuthorMapper;
use Atlas\Fake\Summary\SummaryMapper;
use Atlas\Fake\Reply\ReplyMapper;
use Atlas\Fake\Tagging\TaggingMapper;
use Atlas\Fake\Tag\TagMapper;

class ThreadMapper extends AbstractMapper
{
    protected function setMapperRelations()
    {
        $this->belongsTo('author', AuthorMapper::CLASS);
        $this->hasOne('summary', SummaryMapper::CLASS);
        $this->hasMany('replies', ReplyMapper::CLASS);
        $this->hasMany('taggings', TaggingMapper::CLASS);
        $this->hasManyThrough('tags', TagMapper::CLASS, 'taggings');
    }
}
