<?php
namespace Atlas\Table;

use Atlas\Exception;
use Atlas\Fake\Employee\EmployeeTable;

class TableLocatorTest extends \PHPUnit_Framework_TestCase
{
    protected $tableLocator;

    protected function setUp()
    {
        $this->tableLocator = new TableLocator();
        $this->tableLocator->set(EmployeeTable::CLASS, function () {
            return EmployeeTable::CLASS;
        });
    }

    public function testHas()
    {
        $this->assertFalse($this->tableLocator->has('Atlas\Fake\Employee'));
        $this->assertTrue($this->tableLocator->has(EmployeeTable::CLASS));
    }

    public function testGet()
    {
        $expect = EmployeeTable::CLASS;
        $this->assertSame($expect, $this->tableLocator->get(EmployeeTable::CLASS));

        $this->setExpectedException(Exception::CLASS, "Atlas\Fake\Employee not found in locator");
        $this->tableLocator->get('Atlas\Fake\Employee');
    }
}
