<?php
namespace Atlas\Orm\DataSource\Author;

use Atlas\Orm\DataSource\Reply\ReplyMapper;
use Atlas\Orm\DataSource\Thread\ThreadMapper;
use Atlas\Orm\Mapper\AbstractMapper;

class AuthorMapper extends AbstractMapper
{
    protected function setRelated()
    {
        parent::setRelated(); // test coverage only

        $this->oneToMany('replies', ReplyMapper::CLASS);
        $this->oneToMany('threads', ThreadMapper::CLASS);
    }
}
