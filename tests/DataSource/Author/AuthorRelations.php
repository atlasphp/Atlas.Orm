<?php
namespace Atlas\DataSource\Author;

use Atlas\DataSource\Reply\ReplyMapper;
use Atlas\DataSource\Thread\ThreadMapper;
use Atlas\Mapper\AbstractRelations;

class AuthorRelations extends AbstractRelations
{
    protected function setRelations()
    {
        $this->hasMany('replies', ReplyMapper::CLASS);
        $this->hasMany('threads', ThreadMapper::CLASS);
    }
}
