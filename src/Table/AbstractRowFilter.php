<?php
namespace Atlas\Table;

abstract class AbstractRowFilter
{
    public function forInsert(AbstractTable $table, AbstractRow $row)
    {
        // do nothing
    }

    public function forUpdate(AbstractTable $table, AbstractRow $row)
    {
        // do nothing
    }

    public function forDelete(AbstractTable $table, AbstractRow $row)
    {
        // do nothing
    }
}
