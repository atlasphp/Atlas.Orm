<?php
namespace Atlas\DataSource\Employee;

use Atlas\Mapper\AbstractMapper;

class EmployeeMapper extends AbstractMapper
{
    public function __construct(
        EmployeeTable $table,
        EmployeeRecordFactory $recordFactory,
        EmployeeRelations $relations
    ) {
        parent::__construct($table, $recordFactory, $relations);
    }
}
