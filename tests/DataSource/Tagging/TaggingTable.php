<?php
namespace Atlas\DataSource\Tagging;

use Atlas\Table\AbstractTable;

class TaggingTable extends AbstractTable
{
    public function getTable()
    {
        return 'taggings';
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
