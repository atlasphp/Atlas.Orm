<?php
namespace Atlas\DataSource\Employee;

use Atlas\Mapper\AbstractRecordFactory;

class EmployeeRecordFactory extends AbstractRecordFactory
{
    public function getRecordClass()
    {
        return 'Atlas\DataSource\Employee\EmployeeRecord';
    }

    public function getRecordSetClass()
    {
        return 'Atlas\DataSource\Employee\EmployeeRecordSet';
    }
}
