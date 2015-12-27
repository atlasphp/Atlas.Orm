<?php
namespace Atlas\Orm\Mapper;

use Aura\SqlQuery\Common\Insert;
use Aura\SqlQuery\Common\Update;
use Aura\SqlQuery\Common\Delete;
use PDOStatement;

interface PluginInterface
{
    public function modifyNewRecord(Record $record);

    public function beforeInsert(MapperInterface $mapper, Record $record);

    public function modifyInsert(MapperInterface $mapper, Record $record, Insert $insert);

    public function afterInsert(MapperInterface $mapper, Record $record, Insert $insert, PDOStatement $pdoStatement);

    public function beforeUpdate(MapperInterface $mapper, Record $record);

    public function modifyUpdate(MapperInterface $mapper, Record $record, Update $update);

    public function afterUpdate(MapperInterface $mapper, Record $record, Update $update, PDOStatement $pdoStatement);

    public function beforeDelete(MapperInterface $mapper, Record $record);

    public function afterDelete(MapperInterface $mapper, Record $record, Delete $delete, PDOStatement $pdoStatement);

    public function modifyDelete(MapperInterface $mapper, Record $record, Delete $delete);

}
