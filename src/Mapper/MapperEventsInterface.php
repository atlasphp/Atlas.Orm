<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Orm\Mapper;

use Aura\SqlQuery\Common\Delete;
use Aura\SqlQuery\Common\Insert;
use Aura\SqlQuery\Common\Update;
use PDOStatement;

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
     * Runs before the Insert object is created.
     *
     * @param MapperInterface $mapper The mapper handling the insert.
     *
     * @param RecordInterface $record The record to be inserted.
     *
     */
    public function beforeInsert(MapperInterface $mapper, RecordInterface $record);

    /**
     *
     * Runs after the Insert object is created, but before it is executed.
     *
     * @param MapperInterface $mapper The mapper handling the insert.
     *
     * @param RecordInterface $record The record to be inserted.
     *
     * @param Insert $insert The insert query object to be modified.
     *
     */
    public function modifyInsert(MapperInterface $mapper, RecordInterface $record, Insert $insert);

    /**
     *
     * Runs after the Insert object is executed.
     *
     * @param MapperInterface $mapper The mapper handling the insert.
     *
     * @param RecordInterface $record The record that was inserted.
     *
     * @param Insert $insert The insert query object that was executed.
     *
     * @param PDOStatement $pdoStatement The PDOStatement returned from the insert.
     *
     */
    public function afterInsert(MapperInterface $mapper, RecordInterface $record, Insert $insert, PDOStatement $pdoStatement);

    /**
     *
     * Runs before the Update object is created.
     *
     * @param MapperInterface $mapper The mapper handling the update.
     *
     * @param RecordInterface $record The record to be udpated.
     *
     */
    public function beforeUpdate(MapperInterface $mapper, RecordInterface $record);

    /**
     *
     * Runs after the Update object is created, but before it is executed.
     *
     * @param MapperInterface $mapper The mapper handling the update.
     *
     * @param RecordInterface $record The record to be updated.
     *
     * @param Update $update The update query object to be modified.
     *
     */
    public function modifyUpdate(MapperInterface $mapper, RecordInterface $record, Update $update);

    /**
     *
     * Runs after the Update object is executed.
     *
     * @param MapperInterface $mapper The mapper handling the update.
     *
     * @param RecordInterface $record The record that was updated.
     *
     * @param Update $update The update query object that was executed.
     *
     * @param PDOStatement $pdoStatement The PDOStatement returned from the update.
     *
     */
    public function afterUpdate(MapperInterface $mapper, RecordInterface $record, Update $update, PDOStatement $pdoStatement);

    /**
     *
     * Runs before the Delete object is created.
     *
     * @param MapperInterface $mapper The mapper handling the delete.
     *
     * @param RecordInterface $record The record to be udpated.
     *
     */
    public function beforeDelete(MapperInterface $mapper, RecordInterface $record);

    /**
     *
     * Runs after the Delete object is created, but before it is executed.
     *
     * @param MapperInterface $mapper The mapper handling the delete.
     *
     * @param RecordInterface $record The record to be deleted.
     *
     * @param Delete $delete The delete query object to be modified.
     *
     */
    public function modifyDelete(MapperInterface $mapper, RecordInterface $record, Delete $delete);

    /**
     *
     * Runs after the Delete object is executed.
     *
     * @param MapperInterface $mapper The mapper handling the delete.
     *
     * @param RecordInterface $record The record that was deleted.
     *
     * @param Delete $delete The delete query object that was executed.
     *
     * @param PDOStatement $pdoStatement The PDOStatement returned from the delete.
     *
     */
    public function afterDelete(MapperInterface $mapper, RecordInterface $record, Delete $delete, PDOStatement $pdoStatement);
}
