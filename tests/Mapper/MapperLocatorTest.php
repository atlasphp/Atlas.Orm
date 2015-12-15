<?php
namespace Atlas\Orm\Mapper;

use Atlas\Orm\DataSource\Employee\EmployeeMapper;
use Atlas\Orm\DataSource\Employee\EmployeeRecord;
use Atlas\Orm\DataSource\Employee\EmployeeRecordSet;
use Atlas\Orm\DataSource\Employee\EmployeeRow;
use Atlas\Orm\Exception;
use Atlas\Orm\Mapper\Related;
use Atlas\Orm\Table\RowIdentity;

class MapperLocatorTest extends \PHPUnit_Framework_TestCase
{
    protected $mapperLocator;

    protected function setUp()
    {
        $this->mapperLocator = new MapperLocator();
        $this->mapperLocator->set(EmployeeMapper::CLASS, function () {
            return EmployeeMapper::CLASS;
        });
    }

    public function testHas()
    {
        $this->assertFalse($this->mapperLocator->has('Atlas\Orm\DataSource\Employee'));
        $this->assertTrue($this->mapperLocator->has(EmployeeMapper::CLASS));
        $this->assertTrue($this->mapperLocator->has(EmployeeRecord::CLASS));
        $this->assertTrue($this->mapperLocator->has(EmployeeRecordSet::CLASS));
    }

    public function testGet()
    {
        $expect = EmployeeMapper::CLASS;

        $this->assertSame($expect, $this->mapperLocator->get(EmployeeMapper::CLASS));
        $this->assertSame($expect, $this->mapperLocator->get(EmployeeRecord::CLASS));
        $this->assertSame($expect, $this->mapperLocator->get(EmployeeRecordSet::CLASS));

        $row = new EmployeeRow(new RowIdentity(['id' => null]), []);
        $related = new Related([]);
        $record = new EmployeeRecord($row, $related);
        $this->assertSame($expect, $this->mapperLocator->get($record));

        $this->setExpectedException(Exception::CLASS, "Atlas\Orm\DataSource\Employee not found in mapper locator");
        $this->mapperLocator->get('Atlas\Orm\DataSource\Employee');
    }
}
