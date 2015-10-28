<?php
namespace Atlas\DataSource\Thread;

use Atlas\Table\AbstractTable;
use Atlas\Table\IdentityMap;
use Aura\Sql\ConnectionLocator;
use Aura\SqlQuery\QueryFactory;

class ThreadTable extends AbstractTable
{
    use ThreadTableTrait;

    public function __construct(
        ConnectionLocator $connectionLocator,
        QueryFactory $queryFactory,
        IdentityMap $identityMap,
        ThreadRowFactory $rowFactory,
        ThreadRowFilter $rowFilter
    ) {
        parent::__construct(
            $connectionLocator,
            $queryFactory,
            $identityMap,
            $rowFactory,
            $rowFilter
        );
    }
}
