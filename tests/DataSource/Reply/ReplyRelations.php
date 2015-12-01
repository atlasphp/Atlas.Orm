<?php
namespace Atlas\Orm\DataSource\Reply;

use Atlas\Orm\DataSource\Author\AuthorMapper;
use Atlas\Orm\Mapper\MapperRelations;

class ReplyRelations extends MapperRelations
{
    protected function setRelations()
    {
        $this->manyToOne('author', AuthorMapper::CLASS);
    }
}
