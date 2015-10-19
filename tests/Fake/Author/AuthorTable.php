<?php
namespace Atlas\Fake\Author;

use Atlas\Table\AbstractTable;

class AuthorTable extends AbstractTable
{
    public function getTable()
    {
        return 'authors';
    }

    public function getPrimary()
    {
        return 'author_id';
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
            'author_id' => null,
            'name' => null,
        ];
    }

    public function getRowClass()
    {
        return 'Atlas\Fake\Author\AuthorRow';
    }

    public function getRowSetClass()
    {
        return 'Atlas\Fake\Author\AuthorRowSet';
    }

    public function getRowIdentityClass()
    {
        return 'Atlas\Fake\Author\AuthorRowIdentity';
    }
}
