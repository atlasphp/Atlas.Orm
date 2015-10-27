<?php
namespace Atlas\Mapper;

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
