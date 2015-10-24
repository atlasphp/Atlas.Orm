<?php
namespace Atlas\DataSource\Reply;

use Atlas\Mapper\AbstractRecordFactory;

class ReplyRecordFactory extends AbstractRecordFactory
{
    public function getRecordClass()
    {
        return 'Atlas\DataSource\Reply\ReplyRecord';
    }

    public function getRecordSetClass()
    {
        return 'Atlas\DataSource\Reply\ReplyRecordSet';
    }
}
