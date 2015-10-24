<?php
namespace Atlas\DataSource\Tag;

use Atlas\Mapper\AbstractRecordFactory;

class TagRecordFactory extends AbstractRecordFactory
{
    public function getRecordClass()
    {
        return 'Atlas\DataSource\Tag\TagRecord';
    }

    public function getRecordSetClass()
    {
        return 'Atlas\DataSource\Tag\TagRecordSet';
    }
}
