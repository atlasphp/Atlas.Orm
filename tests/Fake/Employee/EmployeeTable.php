<?php
namespace Atlas\Fake\Employee;

use Atlas\Table\Table;

class EmployeeTable extends Table
{
    public function getTable()
    {
        return 'employee';
    }

    public function getPrimary()
    {
        return 'id';
    }

    public function getAutoinc()
    {
        return true;
    }

    public function getCols()
    {
        return [
            '*',
        ];
    }

    public function getDefault()
    {
        return [
            'id' => null,
        ];
    }

    public function getRowClass()
    {
        return 'Atlas\Fake\Employee\EmployeeRow';
    }

    public function getRowSetClass()
    {
        return 'Atlas\Fake\Employee\EmployeeRowSet';
    }

    public function getRowIdentityClass()
    {
        return 'Atlas\Fake\Employee\EmployeeRowIdentity';
    }
}
