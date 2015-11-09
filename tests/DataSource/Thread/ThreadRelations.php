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
        $this->manyToOne('author', AuthorMapper::CLASS);
        $this->oneToOne('summary', SummaryMapper::CLASS);
        $this->oneToMany('replies', ReplyMapper::CLASS);
        $this->oneToMany('taggings', TaggingMapper::CLASS);
        $this->manyToMany('tags', TagMapper::CLASS, 'taggings');
    }
}
