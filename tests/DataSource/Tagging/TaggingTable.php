<?php
namespace Atlas\DataSource\Tagging;

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
