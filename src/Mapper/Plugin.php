<?php
namespace Atlas\Orm\Mapper;

use Atlas\Orm\Table\Gateway;
use Atlas\Orm\Table\RowInterface;
use Aura\SqlQuery\Common\Insert;
use Aura\SqlQuery\Common\Update;
use Aura\SqlQuery\Common\Delete;
use PDOStatement;

class Plugin implements PluginInterface
{
    public function beforeInsert(MapperInterface $mapper, RecordInterface $record)
    {
        // do nothing
    }

    public function modifyInsert(Gateway $gateway, RowInterface $row, Insert $insert)
    {
        // do nothing
    }

    public function afterInsert(Gateway $gateway, RowInterface $row, Insert $insert, PDOStatement $pdoStatement)
    {
        // do nothing
    }

    public function beforeUpdate(MapperInterface $mapper, RecordInterface $record)
    {
        // do nothing
    }

    public function modifyUpdate(Gateway $gateway, RowInterface $row, Update $update)
    {
        // do nothing
    }

    public function afterUpdate(Gateway $gateway, RowInterface $row, Update $update, PDOStatement $pdoStatement)
    {
        // do nothing
    }

    public function beforeDelete(MapperInterface $mapper, RecordInterface $record)
    {
        // do nothing
    }

    public function modifyDelete(Gateway $gateway, RowInterface $row, Delete $delete)
    {
        // do nothing
    }

    public function afterDelete(Gateway $gateway, RowInterface $row, Delete $delete, PDOStatement $pdoStatement)
    {
        // do nothing
    }

}
