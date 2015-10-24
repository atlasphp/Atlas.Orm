<?php
namespace Atlas\DataSource\Summary;

use Atlas\Table\AbstractRowFactory;

class SummaryRowFactory extends AbstractRowFactory
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
        return SummaryRow::CLASS;
    }

    public function getRowSetClass()
    {
        return SummaryRowSet::CLASS;
    }

    public function getRowIdentityClass()
    {
        return SummaryRowIdentity::CLASS;
    }
}
