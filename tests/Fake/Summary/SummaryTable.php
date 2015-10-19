<?php
namespace Atlas\Fake\Summary;

use Atlas\Table\Table;

class SummaryTable extends Table
{
    public function getTable()
    {
        return 'summaries';
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
        return 'Atlas\Fake\Summary\SummaryRow';
    }

    public function getRowSetClass()
    {
        return 'Atlas\Fake\Summary\SummaryRowSet';
    }

    public function getRowIdentityClass()
    {
        return 'Atlas\Fake\Summary\SummaryRowIdentity';
    }
}
