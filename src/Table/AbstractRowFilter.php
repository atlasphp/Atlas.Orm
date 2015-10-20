<?php
namespace Atlas\Table;

abstract class AbstractRowFilter
{
    public function forInsert(AbstractRow $row)
    {
        // do nothing
    }

    public function forUpdate(AbstractRow $row)
    {
        // do nothing
    }
}
