<?php
namespace Atlas\DataSource\Author;

use Atlas\Mapper\AbstractRecordFactory;

class AuthorRecordFactory extends AbstractRecordFactory
{
    public function getRecordClass()
    {
        return AuthorRecord::CLASS;
    }

    public function getRecordSetClass()
    {
        return AuthorRecordSet::CLASS;
    }
}
