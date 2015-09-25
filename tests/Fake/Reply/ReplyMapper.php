<?php
namespace Atlas\Fake\Reply;

use Atlas\Fake\Author\AuthorMapper;
use Atlas\Mapper\Mapper;

class ReplyMapper extends Mapper
{
    protected function setRelations()
    {
        $this->relations->manyToOne('author', AuthorMapper::CLASS);
    }
}
