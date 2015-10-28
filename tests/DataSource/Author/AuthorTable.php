<?php
namespace Atlas\DataSource\Author;

use Atlas\Table\AbstractTable;
use Atlas\Table\IdentityMap;
use Aura\Sql\ConnectionLocator;
use Aura\SqlQuery\QueryFactory;

class AuthorTable extends AbstractTable
{
    use AuthorTableTrait;

    public function __construct(
        ConnectionLocator $connectionLocator,
        QueryFactory $queryFactory,
        IdentityMap $identityMap,
        AuthorRowFactory $rowFactory,
        AuthorRowFilter $rowFilter
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
