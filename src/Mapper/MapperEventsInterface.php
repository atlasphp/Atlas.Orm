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
 * Events to be invoked during Mapper operations.
 *
 * @package atlas/orm
 *
 */
interface MapperEventsInterface
{
    /**
     *
     * Runs before inserting a Record.
     *
     * @param MapperInterface $mapper The Mapper for the Record.
     *
     * @param RecordInterface $record The Record being worked with.
     *
     * @return void
     *
     */
    public function beforeInsert(MapperInterface $mapper, RecordInterface $record);

    /**
     *
     * Runs after inserting a Record.
     *
     * @param MapperInterface $mapper The Mapper for the Record.
     *
     * @param RecordInterface $record The Record being worked with.
     *
     * @param mixed $result The result of the insert.
     *
     * @return void
     *
     */
    public function afterInsert(MapperInterface $mapper, RecordInterface $record, $result);

    /**
     *
     * Runs before updating a Record.
     *
     * @param MapperInterface $mapper The Mapper for the Record.
     *
     * @param RecordInterface $record The Record being worked with.
     *
     * @return void
     *
     */
    public function beforeUpdate(MapperInterface $mapper, RecordInterface $record);

    /**
     *
     * Runs after updating a Record.
     *
     * @param MapperInterface $mapper The Mapper for the Record.
     *
     * @param RecordInterface $record The Record being worked with.
     *
     * @param mixed $result The result of the update.
     *
     * @return void
     *
     */
    public function afterUpdate(MapperInterface $mapper, RecordInterface $record, $result);

    /**
     *
     * Runs before deleting a Record.
     *
     * @param MapperInterface $mapper The Mapper for the Record.
     *
     * @param RecordInterface $record The Record being worked with.
     *
     * @return void
     *
     */
    public function beforeDelete(MapperInterface $mapper, RecordInterface $record);

    /**
     *
     * Runs after deleting a Record.
     *
     * @param MapperInterface $mapper The Mapper for the Record.
     *
     * @param RecordInterface $record The Record being worked with.
     *
     * @param mixed $result The result of the delete.
     *
     * @return void
     *
     */
    public function afterDelete(MapperInterface $mapper, RecordInterface $record, $result);
}
