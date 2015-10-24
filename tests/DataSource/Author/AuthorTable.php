<?php
namespace Atlas\DataSource\Author;

use Atlas\Table\AbstractTable;

class AuthorTable extends AbstractTable
{
    public function getTable()
    {
        return 'authors';
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
}
