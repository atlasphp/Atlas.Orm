<?php
namespace Atlas\DataSource\Tag;

use Atlas\Table\AbstractTable;
use Atlas\Table\IdentityMap;
use Aura\Sql\ConnectionLocator;
use Aura\SqlQuery\QueryFactory;

class TagTable extends AbstractTable
{
    use TagTableTrait;

    public function __construct(
        ConnectionLocator $connectionLocator,
        QueryFactory $queryFactory,
        IdentityMap $identityMap,
        TagRowFactory $rowFactory,
        TagRowFilter $rowFilter
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
