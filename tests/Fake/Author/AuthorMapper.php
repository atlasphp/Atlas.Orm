<?php
namespace Atlas\Fake\Author;

use Atlas\Fake\Reply\ReplyMapper;
use Atlas\Fake\Thread\ThreadMapper;
use Atlas\Mapper\Mapper;

class AuthorMapper extends Mapper
{
    protected function setMapperRelations()
    {
        $this->hasMany('replies', ReplyMapper::CLASS);
        $this->hasMany('threads', ThreadMapper::CLASS);
    }
}
