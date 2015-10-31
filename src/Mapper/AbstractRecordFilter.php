<?php
namespace Atlas\Mapper;

abstract class AbstractRecordFilter
{
    public function forInsert(AbstractMapper $mapper, AbstractRecord $record)
    {
        // do nothing
    }

    public function forUpdate(AbstractMapper $mapper, AbstractRecord $record)
    {
        // do nothing
    }

    public function forDelete(AbstractMapper $mapper, AbstractRecord $record)
    {
        // do nothing
    }
}
