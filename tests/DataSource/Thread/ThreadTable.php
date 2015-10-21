<?php
namespace Atlas\DataSource\Thread;

use Atlas\Table\AbstractTable;

class ThreadTable extends AbstractTable
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
