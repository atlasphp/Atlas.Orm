<?php
namespace Atlas\DataSource\Tag;

use Atlas\Mapper\AbstractRecordFactory;

class TagRecordFactory extends AbstractRecordFactory
{
    public function getRecordClass()
    {
        return TagRecord::CLASS;
    }

    public function getRecordSetClass()
    {
        return TagRecordSet::CLASS;
    }
}
