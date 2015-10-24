<?php
namespace Atlas\DataSource\Thread;

use Atlas\Table\AbstractTable;

class ThreadTable extends AbstractTable
{
    public function getTable()
    {
        return 'threads';
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
