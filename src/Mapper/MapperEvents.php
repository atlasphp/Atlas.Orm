<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Orm\Mapper;

/**
 *
 * Default events to be invoked during Mapper operations.
 *
 * @package atlas/orm
 *
 */
class MapperEvents implements MapperEventsInterface
{
    /**
     * @inheritdoc
     */
    public function beforeInsert(MapperInterface $mapper, RecordInterface $record)
    {
    }

    /**
     * @inheritdoc
     */
    public function afterInsert(MapperInterface $mapper, RecordInterface $record, $result)
    {
    }

    /**
     * @inheritdoc
     */
    public function beforeUpdate(MapperInterface $mapper, RecordInterface $record)
    {
    }

    /**
     * @inheritdoc
     */
    public function afterUpdate(MapperInterface $mapper, RecordInterface $record, $result)
    {
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete(MapperInterface $mapper, RecordInterface $record)
    {
    }

    /**
     * @inheritdoc
     */
    public function afterDelete(MapperInterface $mapper, RecordInterface $record, $result)
    {
    }
}
