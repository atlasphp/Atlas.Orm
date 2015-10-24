<?php
namespace Atlas\DataSource\Employee;

use Atlas\Table\AbstractTable;

class EmployeeTable extends AbstractTable
{
    public function getTable()
    {
        return 'employee';
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
}
