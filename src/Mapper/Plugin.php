<?php
namespace Atlas\Orm\Mapper;

use Atlas\Orm\Table\GatewayInterface;
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

    public function modifyInsert(GatewayInterface $gateway, RowInterface $row, Insert $insert)
    {
        // do nothing
    }

    public function afterInsert(GatewayInterface $gateway, RowInterface $row, Insert $insert, PDOStatement $pdoStatement)
    {
        // do nothing
    }

    public function beforeUpdate(MapperInterface $mapper, RecordInterface $record)
    {
        // do nothing
    }

    public function modifyUpdate(GatewayInterface $gateway, RowInterface $row, Update $update)
    {
        // do nothing
    }

    public function afterUpdate(GatewayInterface $gateway, RowInterface $row, Update $update, PDOStatement $pdoStatement)
    {
        // do nothing
    }

    public function beforeDelete(MapperInterface $mapper, RecordInterface $record)
    {
        // do nothing
    }

    public function modifyDelete(GatewayInterface $gateway, RowInterface $row, Delete $delete)
    {
        // do nothing
    }

    public function afterDelete(GatewayInterface $gateway, RowInterface $row, Delete $delete, PDOStatement $pdoStatement)
    {
        // do nothing
    }

}
