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
        return ReplyRow::CLASS;
    }

    public function getRowSetClass()
    {
        return ReplyRowSet::CLASS;
    }

    public function getRowIdentityClass()
    {
        return ReplyRowIdentity::CLASS;
    }
}
