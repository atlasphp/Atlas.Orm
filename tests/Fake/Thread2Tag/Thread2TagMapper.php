<?php
namespace Atlas\Fake\Thread2Tag;

use Atlas\Mapper\Mapper;

class Thread2TagMapper extends Mapper
{
    protected function setRelations()
    {
        $this->relations->manyToOne('threads', ThreadMapper::CLASS);
        $this->relations->manyToOne('tags', TagMapper::CLASS);
    }
}
