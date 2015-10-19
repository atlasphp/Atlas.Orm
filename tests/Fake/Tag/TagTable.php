<?php
namespace Atlas\Fake\Tag;

use Atlas\Table\Table;

class TagTable extends Table
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
        return 'Atlas\Fake\Tag\TagRow';
    }

    public function getRowSetClass()
    {
        return 'Atlas\Fake\Tag\TagRowSet';
    }

    public function getRowIdentityClass()
    {
        return 'Atlas\Fake\Tag\TagRowIdentity';
    }
}
