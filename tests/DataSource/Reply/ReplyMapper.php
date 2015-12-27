<?php
namespace Atlas\Orm\DataSource\Reply;

use Atlas\Orm\DataSource\Author\AuthorMapper;
use Atlas\Orm\Mapper\Mapper;

class ReplyMapper extends Mapper
{
    protected function defineRelationship()
    {
        $this->manyToOne('author', AuthorMapper::CLASS);
    }
}
