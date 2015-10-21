<?php
namespace Atlas\DataSource\Reply;

use Atlas\DataSource\Author\AuthorMapper;
use Atlas\Mapper\AbstractMapper;

class ReplyMapper extends AbstractMapper
{
    protected function setMapperRelations()
    {
        $this->belongsTo('author', AuthorMapper::CLASS);
    }
}
