<?php
namespace Atlas\Orm\Mapper;

use Aura\SqlQuery\Common\Insert;
use Aura\SqlQuery\Common\Update;
use Aura\SqlQuery\Common\Delete;
use PDOStatement;

class Plugin implements PluginInterface
{
    public function modifyNewRecord(Record $record)
    {
        // do nothing
    }

    public function beforeInsert(Mapper $mapper, Record $record)
    {
        // do nothing
    }

    public function modifyInsert(Mapper $mapper, Record $record, Insert $insert)
    {
        // do nothing
    }

    public function afterInsert(Mapper $mapper, Record $record, Insert $insert, PDOStatement $pdoStatement)
    {
        // do nothing
    }

    public function beforeUpdate(Mapper $mapper, Record $record)
    {
        // do nothing
    }

    public function modifyUpdate(Mapper $mapper, Record $record, Update $update)
    {
        // do nothing
    }

    public function afterUpdate(Mapper $mapper, Record $record, Update $update, PDOStatement $pdoStatement)
    {
        // do nothing
    }

    public function beforeDelete(Mapper $mapper, Record $record)
    {
        // do nothing
    }

    public function afterDelete(Mapper $mapper, Record $record, Delete $delete, PDOStatement $pdoStatement)
    {
        // do nothing
    }

    public function modifyDelete(Mapper $mapper, Record $record, Delete $delete)
    {
        // do nothing
    }

}
