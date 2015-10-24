<?php
namespace Atlas\DataSource\Employee;

use Atlas\Table\AbstractRowFactory;

class EmployeeRowFactory extends AbstractRowFactory
{
    public function getPrimary()
    {
        return 'id';
    }

    public function getDefault()
    {
        return [
            'id' => null,
        ];
    }

    public function getRowClass()
    {
        return EmployeeRow::CLASS;
    }

    public function getRowSetClass()
    {
        return EmployeeRowSet::CLASS;
    }

    public function getRowIdentityClass()
    {
        return EmployeeRowIdentity::CLASS;
    }
}
