<?php
namespace Atlas\DataSource\Employee;

use Atlas\Table\AbstractTable;
use Atlas\Table\IdentityMap;
use Aura\Sql\ConnectionLocator;
use Aura\SqlQuery\QueryFactory;

class EmployeeTable extends AbstractTable
{
    use EmployeeTableTrait;

    public function __construct(
        ConnectionLocator $connectionLocator,
        QueryFactory $queryFactory,
        IdentityMap $identityMap,
        EmployeeRowFactory $rowFactory,
        EmployeeRowFilter $rowFilter
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
