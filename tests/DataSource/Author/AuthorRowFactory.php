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
        return AuthorRow::CLASS;
    }

    public function getRowSetClass()
    {
        return AuthorRowSet::CLASS;
    }

    public function getRowIdentityClass()
    {
        return AuthorRowIdentity::CLASS;
    }
}
