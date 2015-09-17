<?php
namespace Atlas\Fake\Employee;

use Atlas\Table\Table;

class EmployeeTable extends Table
{
    protected $table = 'employees';
    protected $primary = 'id';
}
