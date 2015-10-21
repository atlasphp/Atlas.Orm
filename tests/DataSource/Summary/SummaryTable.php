<?php
namespace Atlas\DataSource\Summary;

use Atlas\Table\AbstractTable;

class SummaryTable extends AbstractTable
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
        return 'Atlas\DataSource\Summary\SummaryRow';
    }

    public function getRowSetClass()
    {
        return 'Atlas\DataSource\Summary\SummaryRowSet';
    }

    public function getRowIdentityClass()
    {
        return 'Atlas\DataSource\Summary\SummaryRowIdentity';
    }
}
