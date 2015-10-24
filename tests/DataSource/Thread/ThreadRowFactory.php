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
        return 'Atlas\DataSource\Thread\ThreadRow';
    }

    public function getRowSetClass()
    {
        return 'Atlas\DataSource\Thread\ThreadRowSet';
    }

    public function getRowIdentityClass()
    {
        return 'Atlas\DataSource\Thread\ThreadRowIdentity';
    }
}
