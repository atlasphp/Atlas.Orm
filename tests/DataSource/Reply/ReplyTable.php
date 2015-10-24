<?php
namespace Atlas\DataSource\Reply;

use Atlas\Table\AbstractTable;

class ReplyTable extends AbstractTable
{
    public function getTable()
    {
        return 'replies';
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
