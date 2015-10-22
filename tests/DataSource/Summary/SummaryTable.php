<?php
namespace Atlas\DataSource\Summary;

use Atlas\Table\AbstractTable;

class SummaryTable extends AbstractTable
{
    public function getTable()
    {
        return 'summaries';
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
}
