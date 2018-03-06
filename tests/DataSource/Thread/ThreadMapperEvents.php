<?php
namespace Atlas\Orm\DataSource\Thread;

use Atlas\Orm\Mapper\MapperEvents;
use Atlas\Orm\Mapper\MapperInterface;
use Atlas\Orm\Mapper\RecordInterface;
use Aura\SqlQuery\Common\Delete;
use Aura\SqlQuery\Common\Insert;
use Aura\SqlQuery\Common\Update;
use PDOStatement;

class ThreadMapperEvents extends MapperEvents
{
    public static $beforeInsert;

    public function beforeInsert(MapperInterface $mapper, RecordInterface $record)
    {
        if (static::$beforeInsert) {
            call_user_func(static::$beforeInsert, $mapper, $record);
        }
    }
}
