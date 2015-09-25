<?php
namespace Atlas\Fake\Thread;

use Atlas\Mapper\Mapper;

class ThreadMapper extends Mapper
{
    protected function setRelations()
    {
        $this->relations->manyToOne('author', AuthorMapper::CLASS);
        $this->relations->oneToOne('summary', SummaryMapper::CLASS);
        $this->relations->oneToMany('replies', ReplyMapper::CLASS);
        $this->relations->oneToMany('threads2tags', Thread2TagMapper::CLASS);
        $this->relations->manyToMany('tags', TagMapper::CLASS, 'threads2tags');
    }
}
