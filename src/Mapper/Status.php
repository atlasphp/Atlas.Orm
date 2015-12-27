<?php
namespace Atlas\Orm\Mapper;

class Status
{
    // new instance, in memory only
    const IS_NEW = 'IS_NEW';

    // selected, and not yet modified in memory
    const IS_CLEAN = 'IS_CLEAN';

    // selected/inserted/updated, then modified in memory
    const IS_DIRTY = 'IS_DIRTY';

    // marked for deletion but not deleted, modification in memory allowed
    const IS_TRASH = 'IS_TRASH';

    // inserted, and not again modified in memory
    const IS_INSERTED = 'IS_INSERTED';

    // updated, and not again modified in memory
    const IS_UPDATED = 'IS_UPDATED';

    // deleted, modification in memory not allowed
    const IS_DELETED = 'IS_DELETED';

    final private function __construct()
    {
    }
}
