<?php
namespace Atlas\Fake\Author;

use Atlas\Fake\Reply\ReplyMapper;
use Atlas\Fake\Thread\ThreadMapper;
use Atlas\Mapper\AbstractMapper;

class AuthorMapper extends AbstractMapper
{
    protected function setMapperRelations()
    {
        $this->hasMany('replies', ReplyMapper::CLASS);
        $this->hasMany('threads', ThreadMapper::CLASS);
    }
}
