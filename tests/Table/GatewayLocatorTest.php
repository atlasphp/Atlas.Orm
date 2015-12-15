<?php
namespace Atlas\Orm\Table;

use Atlas\Orm\Exception;
use Atlas\Orm\DataSource\Employee\EmployeeTable;

class GatewayLocatorTest extends \PHPUnit_Framework_TestCase
{
    protected $gatewayLocator;

    protected function setUp()
    {
        $this->gatewayLocator = new GatewayLocator();
        $this->gatewayLocator->set(EmployeeTable::CLASS, function () {
            return EmployeeTable::CLASS;
        });
    }

    public function testHas()
    {
        $this->assertFalse($this->gatewayLocator->has('Atlas\Orm\DataSource\Employee'));
        $this->assertTrue($this->gatewayLocator->has(EmployeeTable::CLASS));
    }

    public function testGet()
    {
        $expect = EmployeeTable::CLASS;
        $this->assertSame($expect, $this->gatewayLocator->get(EmployeeTable::CLASS));

        $this->setExpectedException(Exception::CLASS, "Atlas\Orm\DataSource\Employee not found in table locator");
        $this->gatewayLocator->get('Atlas\Orm\DataSource\Employee');
    }
}
