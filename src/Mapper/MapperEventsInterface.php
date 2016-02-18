<?php
namespace Atlas\Orm\Mapper;

interface MapperEventsInterface
{
    public function beforeInsert(MapperInterface $mapper, RecordInterface $record);

    public function afterInsert(MapperInterface $mapper, RecordInterface $record, $result);

    public function beforeUpdate(MapperInterface $mapper, RecordInterface $record);

    public function afterUpdate(MapperInterface $mapper, RecordInterface $record, $result);

    public function beforeDelete(MapperInterface $mapper, RecordInterface $record);

    public function afterDelete(MapperInterface $mapper, RecordInterface $record, $result);
}
