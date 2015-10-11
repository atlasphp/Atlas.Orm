<?php
namespace Atlas\Fake\Employee;

use Atlas\Table\Table;

class EmployeeTable extends Table
{
    protected $primary = 'id';

    protected $cols = [
        'id',
        'name',
        'building',
        'floor',
    ];
}
