<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Orm\Table;

use Aura\SqlQuery\Common\Insert;
use Aura\SqlQuery\Common\Update;
use Aura\SqlQuery\Common\Delete;
use PDOStatement;

/**
 *
 * Default events to be invoked during Table operations.
 *
 * @package atlas/orm
 *
 */
class TableEvents implements TableEventsInterface
{
    /**
     * @inheritdoc
     */
    public function beforeInsert(TableInterface $table, RowInterface $row)
    {
    }

    /**
     * @inheritdoc
     */
    public function modifyInsert(TableInterface $table, RowInterface $row, Insert $insert)
    {
    }

    /**
     * @inheritdoc
     */
    public function afterInsert(TableInterface $table, RowInterface $row, Insert $insert, PDOStatement $pdoStatement)
    {
    }

    /**
     * @inheritdoc
     */
    public function beforeUpdate(TableInterface $table, RowInterface $row)
    {
    }

    /**
     * @inheritdoc
     */
    public function modifyUpdate(TableInterface $table, RowInterface $row, Update $update)
    {
    }

    /**
     * @inheritdoc
     */
    public function afterUpdate(TableInterface $table, RowInterface $row, Update $update, PDOStatement $pdoStatement)
    {
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete(TableInterface $table, RowInterface $row)
    {
    }

    /**
     * @inheritdoc
     */
    public function modifyDelete(TableInterface $table, RowInterface $row, Delete $delete)
    {
    }

    /**
     * @inheritdoc
     */
    public function afterDelete(TableInterface $table, RowInterface $row, Delete $delete, PDOStatement $pdoStatement)
    {
    }
}
