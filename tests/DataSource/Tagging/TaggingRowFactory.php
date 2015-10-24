<?php
namespace Atlas\DataSource\Tagging;

use Atlas\Table\AbstractRowFactory;

class TaggingRowFactory extends AbstractRowFactory
{
    public function getPrimary()
    {
        return 'tagging_id';
    }

    public function getDefault()
    {
        return [
            'tagging_id' => null,
        ];
    }

    public function getRowClass()
    {
        return TaggingRow::CLASS;
    }

    public function getRowSetClass()
    {
        return TaggingRowSet::CLASS;
    }

    public function getRowIdentityClass()
    {
        return TaggingRowIdentity::CLASS;
    }
}
