<?php
namespace Atlas\DataSource\Summary;

use Atlas\Mapper\AbstractRecordFactory;

class SummaryRecordFactory extends AbstractRecordFactory
{
    public function getRecordClass()
    {
        return 'Atlas\DataSource\Summary\SummaryRecord';
    }

    public function getRecordSetClass()
    {
        return 'Atlas\DataSource\Summary\SummaryRecordSet';
    }
}
