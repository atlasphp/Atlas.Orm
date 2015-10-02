<?php
namespace Atlas\Fake\Reply;

use Atlas\Fake\Author\AuthorMapper;
use Atlas\Mapper\Mapper;

class ReplyMapper extends Mapper
{
    protected function setMapperRelations()
    {
        $this->belongsTo('author', AuthorMapper::CLASS);
    }
}
