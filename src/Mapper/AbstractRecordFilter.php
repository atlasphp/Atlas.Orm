<?php
namespace Atlas\Orm\Mapper;

abstract class AbstractRecordFilter
{
    public function forInsert(AbstractRecord $record)
    {
        // do nothing
    }

    public function forUpdate(AbstractRecord $record)
    {
        // do nothing
    }

    public function forDelete(AbstractRecord $record)
    {
        // do nothing
    }
}
