<?php
namespace Atlas\Fake\Reply;

use Atlas\Table\Table;

class ReplyTable extends Table
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
        return 'Atlas\Fake\Reply\ReplyRow';
    }

    public function getRowSetClass()
    {
        return 'Atlas\Fake\Reply\ReplyRowSet';
    }

    public function getRowIdentityClass()
    {
        return 'Atlas\Fake\Reply\ReplyRowIdentity';
    }
}
