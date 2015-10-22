<?php
namespace Atlas\DataSource\Author;

use Atlas\Table\AbstractRowFactory;

class AuthorRowFactory extends AbstractRowFactory
{
    public function getPrimary()
    {
        return 'author_id';
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
        return 'Atlas\DataSource\Author\AuthorRow';
    }

    public function getRowSetClass()
    {
        return 'Atlas\DataSource\Author\AuthorRowSet';
    }

    public function getRowIdentityClass()
    {
        return 'Atlas\DataSource\Author\AuthorRowIdentity';
    }
}
