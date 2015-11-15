<?php
namespace Atlas\Orm\DataSource\Reply;

use Atlas\Orm\DataSource\Author\AuthorMapper;
use Atlas\Orm\Mapper\AbstractRelations;

class ReplyRelations extends AbstractRelations
{
    protected function setRelations()
    {
        $this->manyToOne('author', AuthorMapper::CLASS);
    }
}
