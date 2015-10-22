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
        ];
    }

    public function getRowClass()
    {
        return 'Atlas\DataSource\Tagging\TaggingRow';
    }

    public function getRowSetClass()
    {
        return 'Atlas\DataSource\Tagging\TaggingRowSet';
    }

    public function getRowIdentityClass()
    {
        return 'Atlas\DataSource\Tagging\TaggingRowIdentity';
    }
}
