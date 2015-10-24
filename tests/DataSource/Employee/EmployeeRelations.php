<?php
namespace Atlas\DataSource\Employee;

use Atlas\Mapper\AbstractRelations;

class EmployeeRelations extends AbstractRelations
{
    protected function getNativeMapperClass()
    {
        return EmployeeMapper::CLASS;
    }

    protected function setRelations()
    {
        // no relations
    }
}
