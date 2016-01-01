<?php
namespace Atlas\Orm\Mapper;

use Atlas\Orm\Table\Gateway;
use Atlas\Orm\Table\RowInterface;
use Aura\SqlQuery\Common\Delete;
use Aura\SqlQuery\Common\Insert;
use Aura\SqlQuery\Common\Update;
use PDOStatement;

interface PluginInterface
{
    public function beforeInsert(MapperInterface $mapper, RecordInterface $record);

    public function modifyInsert(Gateway $gateway, RowInterface $row, Insert $insert);

    public function afterInsert(Gateway $gateway, RowInterface $row, Insert $insert, PDOStatement $pdoStatement);

    public function beforeUpdate(MapperInterface $mapper, RecordInterface $record);

    public function modifyUpdate(Gateway $gateway, RowInterface $row, Update $update);

    public function afterUpdate(Gateway $gateway, RowInterface $row, Update $update, PDOStatement $pdoStatement);

    public function beforeDelete(MapperInterface $mapper, RecordInterface $record);

    public function modifyDelete(Gateway $gateway, RowInterface $row, Delete $delete);

    public function afterDelete(Gateway $gateway, RowInterface $row, Delete $delete, PDOStatement $pdoStatement);

}
