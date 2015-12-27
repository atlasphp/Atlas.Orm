<?php
namespace Atlas\Orm\Mapper;

use Aura\SqlQuery\Common\Insert;
use Aura\SqlQuery\Common\Update;
use Aura\SqlQuery\Common\Delete;
use PDOStatement;

interface PluginInterface
{
    public function modifyNewRecord(RecordInterface $record);

    public function beforeInsert(MapperInterface $mapper, RecordInterface $record);

    public function modifyInsert(MapperInterface $mapper, RecordInterface $record, Insert $insert);

    public function afterInsert(MapperInterface $mapper, RecordInterface $record, Insert $insert, PDOStatement $pdoStatement);

    public function beforeUpdate(MapperInterface $mapper, RecordInterface $record);

    public function modifyUpdate(MapperInterface $mapper, RecordInterface $record, Update $update);

    public function afterUpdate(MapperInterface $mapper, RecordInterface $record, Update $update, PDOStatement $pdoStatement);

    public function beforeDelete(MapperInterface $mapper, RecordInterface $record);

    public function afterDelete(MapperInterface $mapper, RecordInterface $record, Delete $delete, PDOStatement $pdoStatement);

    public function modifyDelete(MapperInterface $mapper, RecordInterface $record, Delete $delete);

}
