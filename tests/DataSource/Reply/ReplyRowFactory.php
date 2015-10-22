<?php
namespace Atlas\DataSource\Reply;

use Atlas\Table\AbstractRowFactory;

class ReplyRowFactory extends AbstractRowFactory
{
    public function getPrimary()
    {
        return 'reply_id';
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
