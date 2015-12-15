<?php
namespace Atlas\Orm\Table;

use Aura\SqlQuery\Common\DeleteInterface;
use Aura\SqlQuery\Common\InsertInterface;
use Aura\SqlQuery\Common\UpdateInterface;
use PdoStatement;

class TableEvents
{
    public function modifyNewRow(TableGateway $table, Row $row)
    {
        // do nothing
    }

    public function beforeInsert(TableGateway $table, Row $row)
    {
        // do nothing
    }

    public function modifyInsert(TableGateway $table, Row $row, InsertInterface $insert)
    {
        // do nothing
    }

    public function afterInsert(TableGateway $table, Row $row, InsertInterface $insert, PdoStatement $pdoStatement)
    {
        // do nothing
    }

    public function beforeUpdate(TableGateway $table, Row $row)
    {
        // do nothing
    }

    public function modifyUpdate(TableGateway $table, Row $row, UpdateInterface $update)
    {
        // do nothing
    }

    public function afterUpdate(TableGateway $table, Row $row, UpdateInterface $update, PdoStatement $pdoStatement)
    {
        // do nothing
    }

    public function beforeDelete(TableGateway $table, Row $row)
    {
        // do nothing
    }

    public function modifyDelete(TableGateway $table, Row $row, DeleteInterface $delete)
    {
        // do nothing
    }

    public function afterDelete(TableGateway $table, Row $row, DeleteInterface $delete, PdoStatement $pdoStatement)
    {
        // do nothing
    }
}
