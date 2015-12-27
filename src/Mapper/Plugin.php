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

    public function beforeInsert(MapperInterface $mapper, Record $record)
    {
        // do nothing
    }

    public function modifyInsert(MapperInterface $mapper, Record $record, Insert $insert)
    {
        // do nothing
    }

    public function afterInsert(MapperInterface $mapper, Record $record, Insert $insert, PDOStatement $pdoStatement)
    {
        // do nothing
    }

    public function beforeUpdate(MapperInterface $mapper, Record $record)
    {
        // do nothing
    }

    public function modifyUpdate(MapperInterface $mapper, Record $record, Update $update)
    {
        // do nothing
    }

    public function afterUpdate(MapperInterface $mapper, Record $record, Update $update, PDOStatement $pdoStatement)
    {
        // do nothing
    }

    public function beforeDelete(MapperInterface $mapper, Record $record)
    {
        // do nothing
    }

    public function afterDelete(MapperInterface $mapper, Record $record, Delete $delete, PDOStatement $pdoStatement)
    {
        // do nothing
    }

    public function modifyDelete(MapperInterface $mapper, Record $record, Delete $delete)
    {
        // do nothing
    }

}
