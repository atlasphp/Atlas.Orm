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
    protected function setRelations()
    {
        $this->relations->belongsTo('author', AuthorMapper::CLASS);
        $this->relations->hasOne('summary', SummaryMapper::CLASS);
        $this->relations->hasMany('replies', ReplyMapper::CLASS);
        $this->relations->hasMany('threads2tags', Thread2TagMapper::CLASS);
        $this->relations->hasManyThrough('tags', TagMapper::CLASS, 'threads2tags');
    }
}
