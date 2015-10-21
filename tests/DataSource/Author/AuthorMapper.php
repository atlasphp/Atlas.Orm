<?php
namespace Atlas\DataSource\Author;

use Atlas\DataSource\Reply\ReplyMapper;
use Atlas\DataSource\Thread\ThreadMapper;
use Atlas\Mapper\AbstractMapper;

class AuthorMapper extends AbstractMapper
{
    protected function setMapperRelations()
    {
        $this->hasMany('replies', ReplyMapper::CLASS);
        $this->hasMany('threads', ThreadMapper::CLASS);
    }
}
