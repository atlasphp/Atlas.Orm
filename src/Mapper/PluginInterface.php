<?php
namespace Atlas\Orm\Mapper;

use Atlas\Orm\Table\GatewayInterface;
use Atlas\Orm\Table\RowInterface;
use Aura\SqlQuery\Common\Delete;
use Aura\SqlQuery\Common\Insert;
use Aura\SqlQuery\Common\Update;
use PDOStatement;

interface PluginInterface
{
    public function beforeInsert(MapperInterface $mapper, RecordInterface $record);

    public function modifyInsert(GatewayInterface $gateway, RowInterface $row, Insert $insert);

    public function afterInsert(GatewayInterface $gateway, RowInterface $row, Insert $insert, PDOStatement $pdoStatement);

    public function beforeUpdate(MapperInterface $mapper, RecordInterface $record);

    public function modifyUpdate(GatewayInterface $gateway, RowInterface $row, Update $update);

    public function afterUpdate(GatewayInterface $gateway, RowInterface $row, Update $update, PDOStatement $pdoStatement);

    public function beforeDelete(MapperInterface $mapper, RecordInterface $record);

    public function modifyDelete(GatewayInterface $gateway, RowInterface $row, Delete $delete);

    public function afterDelete(GatewayInterface $gateway, RowInterface $row, Delete $delete, PDOStatement $pdoStatement);

}
