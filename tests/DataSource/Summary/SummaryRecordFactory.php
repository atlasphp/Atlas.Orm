<?php
namespace Atlas\DataSource\Summary;

use Atlas\Mapper\AbstractRecordFactory;

class SummaryRecordFactory extends AbstractRecordFactory
{
    public function getRecordClass()
    {
        return SummaryRecord::CLASS;
    }

    public function getRecordSetClass()
    {
        return SummaryRecordSet::CLASS;
    }
}
