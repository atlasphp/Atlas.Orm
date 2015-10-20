<?php
namespace Atlas\Fake\Reply;

use Atlas\Fake\Author\AuthorMapper;
use Atlas\Mapper\AbstractMapper;

class ReplyMapper extends AbstractMapper
{
    protected function setMapperRelations()
    {
        $this->belongsTo('author', AuthorMapper::CLASS);
    }
}
