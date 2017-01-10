<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Orm\Table;

use Aura\SqlQuery\Common\Delete;
use Aura\SqlQuery\Common\Insert;
use Aura\SqlQuery\Common\Update;
use PDOStatement;

/**
 *
 * Events to be invoked during Table operations.
 *
 * @package atlas/orm
 *
 */
interface TableEventsInterface
{
    /**
     *
     * Runs after a newly-selected row is instantiated, but before it is
     * identity-mapped.
     *
     * @param TableInterface $table The table handling the row creation.
     *
     * @param RowInterface $row The newly-selected row.
     *
     */
    public function modifySelectedRow(TableInterface $table, RowInterface $row);

    /**
     *
     * Runs before the Insert object is created.
     *
     * @param TableInterface $table The table handling the insert.
     *
     * @param RowInterface $row The row to be inserted.
     *
     */
    public function beforeInsert(TableInterface $table, RowInterface $row);

    /**
     *
     * Runs after the Insert object is created, but before it is executed.
     *
     * @param TableInterface $table The table handling the insert.
     *
     * @param RowInterface $row The row to be inserted.
     *
     * @param Insert $insert The insert query object to be modified.
     *
     */
    public function modifyInsert(TableInterface $table, RowInterface $row, Insert $insert);

    /**
     *
     * Runs after the Insert object is executed.
     *
     * @param TableInterface $table The table handling the insert.
     *
     * @param RowInterface $row The row that was inserted.
     *
     * @param Insert $insert The insert query object that was executed.
     *
     * @param PDOStatement $pdoStatement The PDOStatement returned from the insert.
     *
     */
    public function afterInsert(TableInterface $table, RowInterface $row, Insert $insert, PDOStatement $pdoStatement);

    /**
     *
     * Runs before the Update object is created.
     *
     * @param TableInterface $table The table handling the update.
     *
     * @param RowInterface $row The row to be udpated.
     *
     */
    public function beforeUpdate(TableInterface $table, RowInterface $row);

    /**
     *
     * Runs after the Update object is created, but before it is executed.
     *
     * @param TableInterface $table The table handling the update.
     *
     * @param RowInterface $row The row to be updated.
     *
     * @param Update $update The update query object to be modified.
     *
     */
    public function modifyUpdate(TableInterface $table, RowInterface $row, Update $update);

    /**
     *
     * Runs after the Update object is executed.
     *
     * @param TableInterface $table The table handling the update.
     *
     * @param RowInterface $row The row that was updated.
     *
     * @param Update $update The update query object that was executed.
     *
     * @param PDOStatement $pdoStatement The PDOStatement returned from the update.
     *
     */
    public function afterUpdate(TableInterface $table, RowInterface $row, Update $update, PDOStatement $pdoStatement);

    /**
     *
     * Runs before the Delete object is created.
     *
     * @param TableInterface $table The table handling the delete.
     *
     * @param RowInterface $row The row to be udpated.
     *
     */
    public function beforeDelete(TableInterface $table, RowInterface $row);

    /**
     *
     * Runs after the Delete object is created, but before it is executed.
     *
     * @param TableInterface $table The table handling the delete.
     *
     * @param RowInterface $row The row to be deleted.
     *
     * @param Delete $delete The delete query object to be modified.
     *
     */
    public function modifyDelete(TableInterface $table, RowInterface $row, Delete $delete);

    /**
     *
     * Runs after the Delete object is executed.
     *
     * @param TableInterface $table The table handling the delete.
     *
     * @param RowInterface $row The row that was deleted.
     *
     * @param Delete $delete The delete query object that was executed.
     *
     * @param PDOStatement $pdoStatement The PDOStatement returned from the delete.
     *
     */
    public function afterDelete(TableInterface $table, RowInterface $row, Delete $delete, PDOStatement $pdoStatement);
}
