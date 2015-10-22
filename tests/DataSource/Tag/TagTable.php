<?php
namespace Atlas\DataSource\Tag;

use Atlas\Table\AbstractTable;

class TagTable extends AbstractTable
{
    public function getTable()
    {
        return 'tags';
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
