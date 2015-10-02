<?php
namespace Atlas\Fake\Thread;

use Atlas\Mapper\Mapper;
use Atlas\Fake\Author\AuthorMapper;
use Atlas\Fake\Summary\SummaryMapper;
use Atlas\Fake\Reply\ReplyMapper;
use Atlas\Fake\Thread2Tag\Thread2TagMapper;
use Atlas\Fake\Tag\TagMapper;

class ThreadMapper extends Mapper
{
    protected function setMapperRelations()
    {
        $this->belongsTo('author', AuthorMapper::CLASS);
        $this->hasOne('summary', SummaryMapper::CLASS);
        $this->hasMany('replies', ReplyMapper::CLASS);
        $this->hasMany('threads2tags', Thread2TagMapper::CLASS);
        $this->hasManyThrough('tags', TagMapper::CLASS, 'threads2tags');
    }
}
