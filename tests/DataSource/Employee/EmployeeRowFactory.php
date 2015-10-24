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
        return 'Atlas\DataSource\Employee\EmployeeRow';
    }

    public function getRowSetClass()
    {
        return 'Atlas\DataSource\Employee\EmployeeRowSet';
    }

    public function getRowIdentityClass()
    {
        return 'Atlas\DataSource\Employee\EmployeeRowIdentity';
    }
}
