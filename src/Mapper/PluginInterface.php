<?php
namespace Atlas\Orm\Mapper;

use Atlas\Orm\Table\TableInterface;
use Atlas\Orm\Table\RowInterface;
use Aura\SqlQuery\Common\Delete;
use Aura\SqlQuery\Common\Insert;
use Aura\SqlQuery\Common\Update;
use PDOStatement;

interface PluginInterface
{
    public function beforeInsert(MapperInterface $mapper, RecordInterface $record);

    public function modifyInsert(TableInterface $table, RowInterface $row, Insert $insert);

    public function afterInsert(TableInterface $table, RowInterface $row, Insert $insert, PDOStatement $pdoStatement);

    public function beforeUpdate(MapperInterface $mapper, RecordInterface $record);

    public function modifyUpdate(TableInterface $table, RowInterface $row, Update $update);

    public function afterUpdate(TableInterface $table, RowInterface $row, Update $update, PDOStatement $pdoStatement);

    public function beforeDelete(MapperInterface $mapper, RecordInterface $record);

    public function modifyDelete(TableInterface $table, RowInterface $row, Delete $delete);

    public function afterDelete(TableInterface $table, RowInterface $row, Delete $delete, PDOStatement $pdoStatement);

}
