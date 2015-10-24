<?php
namespace Atlas\DataSource\Thread;

use Atlas\Mapper\AbstractRecordFactory;

class ThreadRecordFactory extends AbstractRecordFactory
{
    public function getRecordClass()
    {
        return 'Atlas\DataSource\Thread\ThreadRecord';
    }

    public function getRecordSetClass()
    {
        return 'Atlas\DataSource\Thread\ThreadRecordSet';
    }
}
