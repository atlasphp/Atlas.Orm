<?php
namespace Atlas\Fake\Author;

use Atlas\Fake\Reply\ReplyMapper;
use Atlas\Fake\Thread\ThreadMapper;
use Atlas\Mapper\Mapper;

class AuthorMapper extends Mapper
{
    protected function setRelations()
    {
        $this->relations->hasMany('replies', ReplyMapper::CLASS);
        $this->relations->hasMany('threads', ThreadMapper::CLASS);
    }
}
