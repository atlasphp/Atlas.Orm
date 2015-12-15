<?php
namespace Atlas\Orm\Table;

use Aura\SqlQuery\Common\DeleteInterface;
use Aura\SqlQuery\Common\InsertInterface;
use Aura\SqlQuery\Common\UpdateInterface;
use PdoStatement;

class TableEvents
{
    public function modifyNewRow(Table $table, Row $row)
    {
        // do nothing
    }

    public function beforeInsert(Table $table, Row $row)
    {
        // do nothing
    }

    public function modifyInsert(Table $table, Row $row, InsertInterface $insert)
    {
        // do nothing
    }

    public function afterInsert(Table $table, Row $row, InsertInterface $insert, PdoStatement $pdoStatement)
    {
        // do nothing
    }

    public function beforeUpdate(Table $table, Row $row)
    {
        // do nothing
    }

    public function modifyUpdate(Table $table, Row $row, UpdateInterface $update)
    {
        // do nothing
    }

    public function afterUpdate(Table $table, Row $row, UpdateInterface $update, PdoStatement $pdoStatement)
    {
        // do nothing
    }

    public function beforeDelete(Table $table, Row $row)
    {
        // do nothing
    }

    public function modifyDelete(Table $table, Row $row, DeleteInterface $delete)
    {
        // do nothing
    }

    public function afterDelete(Table $table, Row $row, DeleteInterface $delete, PdoStatement $pdoStatement)
    {
        // do nothing
    }
}
