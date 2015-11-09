<?php
namespace Atlas\DataSource\Author;

use Atlas\DataSource\Reply\ReplyMapper;
use Atlas\DataSource\Thread\ThreadMapper;
use Atlas\Mapper\AbstractRelations;

class AuthorRelations extends AbstractRelations
{
    protected function setRelations()
    {
        $this->oneToMany('replies', ReplyMapper::CLASS);
        $this->oneToMany('threads', ThreadMapper::CLASS);
    }
}
