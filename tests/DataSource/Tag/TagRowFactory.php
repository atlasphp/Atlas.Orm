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
        ];
    }

    public function getRowClass()
    {
        return 'Atlas\DataSource\Tag\TagRow';
    }

    public function getRowSetClass()
    {
        return 'Atlas\DataSource\Tag\TagRowSet';
    }

    public function getRowIdentityClass()
    {
        return 'Atlas\DataSource\Tag\TagRowIdentity';
    }
}
