<?php
namespace Atlas\DataSource\Tag;

use Atlas\Table\AbstractTable;

class TagTable extends AbstractTable
{
    public function getTable()
    {
        return 'tags';
    }

    public function getPrimary()
    {
        return 'tag_id';
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
