<?php
namespace Atlas\DataSource\Reply;

use Atlas\Mapper\AbstractRecordFactory;

class ReplyRecordFactory extends AbstractRecordFactory
{
    public function getRecordClass()
    {
        return ReplyRecord::CLASS;
    }

    public function getRecordSetClass()
    {
        return ReplyRecordSet::CLASS;
    }
}
