<?php
namespace Atlas\Fake\Thread;

use Atlas\Table\Table;

class ThreadTable extends Table
{
    public function getTable()
    {
        return 'threads';
    }

    public function getPrimary()
    {
        return 'thread_id';
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
            'thread_id' => null,
        ];
    }

    public function getRowClass()
    {
        return 'Atlas\Fake\Thread\ThreadRow';
    }

    public function getRowSetClass()
    {
        return 'Atlas\Fake\Thread\ThreadRowSet';
    }

    public function getRowIdentityClass()
    {
        return 'Atlas\Fake\Thread\ThreadRowIdentity';
    }
}
