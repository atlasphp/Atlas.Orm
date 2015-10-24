<?php
namespace Atlas\DataSource\Thread;

use Atlas\DataSource\Author\AuthorMapper;
use Atlas\DataSource\Summary\SummaryMapper;
use Atlas\DataSource\Reply\ReplyMapper;
use Atlas\DataSource\Tagging\TaggingMapper;
use Atlas\DataSource\Tag\TagMapper;
use Atlas\Mapper\AbstractRelations;

class ThreadRelations extends AbstractRelations
{
    protected function setRelations()
    {
        $this->belongsTo('author', AuthorMapper::CLASS);
        $this->hasOne('summary', SummaryMapper::CLASS);
        $this->hasMany('replies', ReplyMapper::CLASS);
        $this->hasMany('taggings', TaggingMapper::CLASS);
        $this->hasManyThrough('tags', TagMapper::CLASS, 'taggings');
    }
}
