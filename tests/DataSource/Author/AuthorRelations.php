<?php
namespace Atlas\Orm\DataSource\Author;

use Atlas\Orm\DataSource\Reply\ReplyMapper;
use Atlas\Orm\DataSource\Thread\ThreadMapper;
use Atlas\Orm\Mapper\AbstractRelations;

class AuthorRelations extends AbstractRelations
{
    protected function setRelations()
    {
        $this->oneToMany('replies', ReplyMapper::CLASS);
        $this->oneToMany('threads', ThreadMapper::CLASS);
    }
}
