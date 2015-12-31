<?php
namespace Atlas\Orm\Mapper;

use Atlas\Orm\Table\RowInterface;
use Aura\SqlQuery\Common\Delete;
use Aura\SqlQuery\Common\Insert;
use Aura\SqlQuery\Common\Update;
use PDOStatement;

interface PluginInterface
{
    public function modifyNewRecord(RecordInterface $record);

    public function beforeInsert(MapperInterface $mapper, RecordInterface $record);

    public function modifyInsert(RowInterface $row, Insert $insert);

    public function afterInsert(MapperInterface $mapper, RecordInterface $record, Insert $insert, PDOStatement $pdoStatement);

    public function beforeUpdate(MapperInterface $mapper, RecordInterface $record);

    public function modifyUpdate(RowInterface $row, Update $update);

    public function afterUpdate(MapperInterface $mapper, RecordInterface $record, Update $update, PDOStatement $pdoStatement);

    public function beforeDelete(MapperInterface $mapper, RecordInterface $record);

    public function modifyDelete(RowInterface $row, Delete $delete);

    public function afterDelete(MapperInterface $mapper, RecordInterface $record, Delete $delete, PDOStatement $pdoStatement);

}
