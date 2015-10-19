<?php
namespace Atlas\Fake\Tagging;

use Atlas\Table\AbstractTable;

class TaggingTable extends AbstractTable
{
    public function getTable()
    {
        return 'taggings';
    }

    public function getPrimary()
    {
        return 'tagging_id';
    }

    public function getAutoinc()
    {
        return true;
    }

    public function getCols()
    {
        return [
            '*',
        ];
    }

    public function getDefault()
    {
        return [
            'tagging_id' => null,
        ];
    }

    public function getRowClass()
    {
        return 'Atlas\Fake\Tagging\TaggingRow';
    }

    public function getRowSetClass()
    {
        return 'Atlas\Fake\Tagging\TaggingRowSet';
    }

    public function getRowIdentityClass()
    {
        return 'Atlas\Fake\Tagging\TaggingRowIdentity';
    }
}
