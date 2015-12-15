<?php
namespace Atlas\Orm\Mapper;

class MapperEvents
{
    public function modifyNewRecord(Mapper $mapper, Record $record)
    {
        // do nothing
    }

    public function beforeInsert(Mapper $mapper, Record $record)
    {
        // do nothing
    }

    public function beforeUpdate(Mapper $mapper, Record $record)
    {
        // do nothing
    }

    public function beforeDelete(Mapper $mapper, Record $record)
    {
        // do nothing
    }
}
