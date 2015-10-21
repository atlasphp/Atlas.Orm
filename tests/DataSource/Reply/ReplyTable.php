<?php
namespace Atlas\DataSource\Reply;

use Atlas\Table\AbstractTable;

class ReplyTable extends AbstractTable
{
    public function getTable()
    {
        return 'replies';
    }

    public function getPrimary()
    {
        return 'reply_id';
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
            'reply_id' => null,
        ];
    }

    public function getRowClass()
    {
        return 'Atlas\DataSource\Reply\ReplyRow';
    }

    public function getRowSetClass()
    {
        return 'Atlas\DataSource\Reply\ReplyRowSet';
    }

    public function getRowIdentityClass()
    {
        return 'Atlas\DataSource\Reply\ReplyRowIdentity';
    }
}
