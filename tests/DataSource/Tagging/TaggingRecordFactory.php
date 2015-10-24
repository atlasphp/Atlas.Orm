<?php
namespace Atlas\DataSource\Tagging;

use Atlas\Mapper\AbstractRecordFactory;

class TaggingRecordFactory extends AbstractRecordFactory
{
    public function getRecordClass()
    {
        return TaggingRecord::CLASS;
    }

    public function getRecordSetClass()
    {
        return TaggingRecordSet::CLASS;
    }
}
