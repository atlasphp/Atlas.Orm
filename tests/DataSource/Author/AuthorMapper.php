<?php
namespace Atlas\Orm\DataSource\Author;

use Atlas\Orm\DataSource\Reply\ReplyMapper;
use Atlas\Orm\DataSource\Thread\ThreadMapper;
use Atlas\Orm\Mapper\Mapper;

class AuthorMapper extends Mapper
{
    protected function setRelated()
    {
        $this->oneToMany('replies', ReplyMapper::CLASS);
        $this->oneToMany('threads', ThreadMapper::CLASS);
    }
}
