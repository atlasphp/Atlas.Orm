<?php
namespace Atlas\DataSource\Employee;

use Atlas\Mapper\AbstractRecordFactory;

class EmployeeRecordFactory extends AbstractRecordFactory
{
    public function getRecordClass()
    {
        return EmployeeRecord::CLASS;
    }

    public function getRecordSetClass()
    {
        return EmployeeRecordSet::CLASS;
    }
}
