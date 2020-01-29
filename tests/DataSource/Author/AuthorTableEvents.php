<?php
namespace Atlas\Orm\DataSource\Author;

use Atlas\Orm\Table\TableEvents;
use Atlas\Orm\Table\RowInterface;
use Atlas\Orm\Table\TableInterface;

class AuthorTableEvents extends TableEvents
{
    public function beforeInsert(TableInterface $table, RowInterface $row)
    {
        return $row->getArrayCopy();
    }

    public function beforeUpdate(TableInterface $table, RowInterface $row)
    {
        $init = $table->getIdentityMap()->getInitial($row);
        return $row->getArrayDiff($init);
    }
}
