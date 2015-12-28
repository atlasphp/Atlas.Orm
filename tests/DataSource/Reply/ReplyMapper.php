<?php
namespace Atlas\Orm\DataSource\Reply;

use Atlas\Orm\DataSource\Author\AuthorMapper;
use Atlas\Orm\Mapper\AbstractMapper;

class ReplyMapper extends AbstractMapper
{
    protected function setRelated()
    {
        $this->manyToOne('author', AuthorMapper::CLASS);
    }
}
