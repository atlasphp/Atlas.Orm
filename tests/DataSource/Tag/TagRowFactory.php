<?php
namespace Atlas\DataSource\Tag;

use Atlas\Table\AbstractRowFactory;

class TagRowFactory extends AbstractRowFactory
{
    public function getPrimary()
    {
        return 'tag_id';
    }

    public function getDefault()
    {
        return [
            'tag_id' => null,
            'name' => null,
        ];
    }
}
