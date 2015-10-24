<?php
namespace Atlas\DataSource\Thread;

use Atlas\Mapper\AbstractRecordFactory;

class ThreadRecordFactory extends AbstractRecordFactory
{
    public function getRecordClass()
    {
        return ThreadRecord::CLASS;
    }

    public function getRecordSetClass()
    {
        return ThreadRecordSet::CLASS;
    }
}
