<?php
namespace Atlas\DataSource\Employee;

use Atlas\Table\AbstractRow;
use Atlas\Table\AbstractRowFilter;

class EmployeeRowFilter extends AbstractRowFilter
{
    public function forInsert(AbstractRow $row)
    {
        // do nothing
    }

    public function forUpdate(AbstractRow $row)
    {
        // do nothing
    }

    public function forDelete(AbstractRow $row)
    {
        // do nothing
    }
}
