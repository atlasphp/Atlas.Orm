<?php
namespace Atlas\DataSource\Tagging;

use Atlas\Mapper\AbstractRecordFactory;

class TaggingRecordFactory extends AbstractRecordFactory
{
    public function getRecordClass()
    {
        return 'Atlas\DataSource\Tagging\TaggingRecord';
    }

    public function getRecordSetClass()
    {
        return 'Atlas\DataSource\Tagging\TaggingRecordSet';
    }
}
