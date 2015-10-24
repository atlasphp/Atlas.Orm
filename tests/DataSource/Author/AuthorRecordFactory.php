<?php
namespace Atlas\DataSource\Author;

use Atlas\Mapper\AbstractRecordFactory;

class AuthorRecordFactory extends AbstractRecordFactory
{
    public function getRecordClass()
    {
        return 'Atlas\DataSource\Author\AuthorRecord';
    }

    public function getRecordSetClass()
    {
        return 'Atlas\DataSource\Author\AuthorRecordSet';
    }
}
