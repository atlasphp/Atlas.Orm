<?php
namespace Atlas\Orm\Table;

use Aura\SqlQuery\Common\DeleteInterface;
use Aura\SqlQuery\Common\InsertInterface;
use Aura\SqlQuery\Common\UpdateInterface;
use PdoStatement;

class TableEvents
{
    public function beforeInsert(TableInterface $table, Row $row)
    {
        // do nothing
    }

    public function modifyInsert(TableInterface $table, Row $row, InsertInterface $insert)
    {
        // do nothing
    }

    public function afterInsert(TableInterface $table, Row $row, InsertInterface $insert, PdoStatement $pdoStatement)
    {
        // do nothing
    }

    public function beforeUpdate(TableInterface $table, Row $row)
    {
        // do nothing
    }

    public function modifyUpdate(TableInterface $table, Row $row, UpdateInterface $update)
    {
        // do nothing
    }

    public function afterUpdate(TableInterface $table, Row $row, UpdateInterface $update, PdoStatement $pdoStatement)
    {
        // do nothing
    }

    public function beforeDelete(TableInterface $table, Row $row)
    {
        // do nothing
    }

    public function modifyDelete(TableInterface $table, Row $row, DeleteInterface $delete)
    {
        // do nothing
    }

    public function afterDelete(TableInterface $table, Row $row, DeleteInterface $delete, PdoStatement $pdoStatement)
    {
        // do nothing
    }
}
