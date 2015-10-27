<?php
namespace Atlas\DataSource\Tagging;

use Atlas\Table\AbstractRow;
use Atlas\Table\AbstractRowFilter;

class TaggingRowFilter extends AbstractRowFilter
{
    public function forInsert(AbstractRow $row)
    {
        // do nothing
    }

    public function forUpdate(AbstractRow $row)
    {
        // do nothing
    }

    public function forDelete(AbstractRow $row)
    {
        // do nothing
    }
}
