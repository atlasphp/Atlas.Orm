<?php
namespace Atlas\Orm\Mapper;

use Aura\SqlQuery\Common\Insert;
use Aura\SqlQuery\Common\Update;
use Aura\SqlQuery\Common\Delete;
use PDOStatement;

interface PluginInterface
{
    public function modifyNewRecord(Record $record);

    public function beforeInsert(Mapper $mapper, Record $record);

    public function modifyInsert(Mapper $mapper, Record $record, Insert $insert);

    public function afterInsert(Mapper $mapper, Record $record, Insert $insert, PDOStatement $pdoStatement);

    public function beforeUpdate(Mapper $mapper, Record $record);

    public function modifyUpdate(Mapper $mapper, Record $record, Update $update);

    public function afterUpdate(Mapper $mapper, Record $record, Update $update, PDOStatement $pdoStatement);

    public function beforeDelete(Mapper $mapper, Record $record);

    public function afterDelete(Mapper $mapper, Record $record, Delete $delete, PDOStatement $pdoStatement);

    public function modifyDelete(Mapper $mapper, Record $record, Delete $delete);

}
