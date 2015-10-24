<?php
namespace Atlas\DataSource\Thread;

use Atlas\Table\AbstractRowFactory;

class ThreadRowFactory extends AbstractRowFactory
{
    public function getPrimary()
    {
        return 'thread_id';
    }

    public function getDefault()
    {
        return [
            'thread_id' => null,
        ];
    }

    public function getRowClass()
    {
        return ThreadRow::CLASS;
    }

    public function getRowSetClass()
    {
        return ThreadRowSet::CLASS;
    }

    public function getRowIdentityClass()
    {
        return ThreadRowIdentity::CLASS;
    }
}
