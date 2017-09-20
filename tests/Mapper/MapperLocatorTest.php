<?php
namespace Atlas\Orm\Mapper;

use Atlas\Orm\DataSource\Employee\EmployeeMapper;
use Atlas\Orm\DataSource\Employee\EmployeeRecord;
use Atlas\Orm\DataSource\Employee\EmployeeRecordSet;
use Atlas\Orm\DataSource\Employee\EmployeeTable;
use Atlas\Orm\Exception;
use Atlas\Orm\Mapper\Related;
use Atlas\Orm\Table\Row;
use Atlas\Orm\Table\Primary;

class MapperLocatorTest extends \PHPUnit\Framework\TestCase
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
        $this->assertFalse($this->mapperLocator->has('NoSuchMapper'));
        $this->assertTrue($this->mapperLocator->has(EmployeeMapper::CLASS));
    }

    public function testGet()
    {
        $expect = EmployeeMapper::CLASS;
        $this->assertSame($expect, $this->mapperLocator->get(EmployeeMapper::CLASS));

        $this->expectException(Exception::CLASS, "NoSuchMapper not found in mapper locator");
        $this->mapperLocator->get('NoSuchMapper');
    }
}
