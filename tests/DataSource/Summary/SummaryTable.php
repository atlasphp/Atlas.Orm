<?php
namespace Atlas\DataSource\Summary;

use Atlas\Table\AbstractTable;
use Atlas\Table\IdentityMap;
use Aura\Sql\ConnectionLocator;
use Aura\SqlQuery\QueryFactory;

class SummaryTable extends AbstractTable
{
    use SummaryTableTrait;

    public function __construct(
        ConnectionLocator $connectionLocator,
        QueryFactory $queryFactory,
        IdentityMap $identityMap,
        SummaryRowFactory $rowFactory,
        SummaryRowFilter $rowFilter
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
