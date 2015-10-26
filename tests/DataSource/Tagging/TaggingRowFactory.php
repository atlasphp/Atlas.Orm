<?php
namespace Atlas\DataSource\Tagging;

use Atlas\Table\AbstractRowFactory;

class TaggingRowFactory extends AbstractRowFactory
{
    public function getPrimary()
    {
        return 'tagging_id';
    }

    public function getDefault()
    {
        return [
            'tagging_id' => null,
            'thread_id' => null,
            'tag_id' => null,
        ];
    }
}
