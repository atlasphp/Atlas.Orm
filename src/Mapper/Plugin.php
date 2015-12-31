<?php
namespace Atlas\Orm\Mapper;

use Atlas\Orm\Table\RowInterface;
use Aura\SqlQuery\Common\Insert;
use Aura\SqlQuery\Common\Update;
use Aura\SqlQuery\Common\Delete;
use PDOStatement;

class Plugin implements PluginInterface
{
    public function modifyNewRecord(RecordInterface $record)
    {
        // do nothing
    }

    public function beforeInsert(MapperInterface $mapper, RecordInterface $record)
    {
        // do nothing
    }

    public function modifyInsert(RowInterface $row, Insert $insert)
    {
        // do nothing
    }

    public function afterInsert(MapperInterface $mapper, RecordInterface $record, Insert $insert, PDOStatement $pdoStatement)
    {
        // do nothing
    }

    public function beforeUpdate(MapperInterface $mapper, RecordInterface $record)
    {
        // do nothing
    }

    public function modifyUpdate(RowInterface $row, Update $update)
    {
        // do nothing
    }

    public function afterUpdate(MapperInterface $mapper, RecordInterface $record, Update $update, PDOStatement $pdoStatement)
    {
        // do nothing
    }

    public function beforeDelete(MapperInterface $mapper, RecordInterface $record)
    {
        // do nothing
    }

    public function modifyDelete(RowInterface $row, Delete $delete)
    {
        // do nothing
    }

    public function afterDelete(MapperInterface $mapper, RecordInterface $record, Delete $delete, PDOStatement $pdoStatement)
    {
        // do nothing
    }

}
