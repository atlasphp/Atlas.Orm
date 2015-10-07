<?php
namespace Atlas\Mapper;

use Atlas\Exception;
use Atlas\Fake\Employee\EmployeeMapper;
use Atlas\Fake\Employee\EmployeeRecord;
use Atlas\Fake\Employee\EmployeeRecordSet;
use Atlas\Fake\Employee\EmployeeRow;
use Atlas\Fake\Employee\EmployeeRowIdentity;
use Atlas\Mapper\Related;

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
        $this->assertFalse($this->mapperLocator->has('Atlas\Fake\Employee'));
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

        $row = new EmployeeRow(new EmployeeRowIdentity(['id' => null]), []);
        $related = new Related([]);
        $record = new EmployeeRecord($row, $related);
        $this->assertSame($expect, $this->mapperLocator->get($record));

        $this->setExpectedException(Exception::CLASS, "Atlas\Fake\Employee not found in locator");
        $this->mapperLocator->get('Atlas\Fake\Employee');
    }
}
