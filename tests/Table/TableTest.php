<?php
namespace Atlas\Orm\Table;

use Atlas\Orm\DataSource\Employee\EmployeeTable;
use Atlas\Orm\DataSource\Student\StudentTable;

class TableTest extends \PHPUnit_Framework_TestCase
{
    public function testGetRowClass()
    {
        // generic row
        $table = new StudentTable();
        $actual = $table->getRowClass();
        $expect = 'Atlas\Orm\Table\Row';
        $this->assertSame($expect, $actual);

        // custom row
        $table = new EmployeeTable();
        $actual = $table->getRowClass();
        $expect = 'Atlas\Orm\DataSource\Employee\EmployeeRow';
        $this->assertSame($expect, $actual);
    }
}
