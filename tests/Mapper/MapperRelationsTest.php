<?php
namespace Atlas\Mapper;

use Atlas\Exception;
use Atlas\DataSource\Employee\EmployeeMapper;
use Atlas\DataSource\Employee\EmployeeRecord;
use Atlas\DataSource\Employee\EmployeeRecordSet;
use Atlas\DataSource\Employee\EmployeeRow;
use Atlas\Relation\HasManyThrough;

class MapperRelationsTest extends \PHPUnit_Framework_TestCase
{
    protected $mapperLocator;
    protected $mapperRelations;

    protected function setUp()
    {
        $mapperLocator = new MapperLocator();
        $mapperLocator->set(EmployeeMapper::CLASS, function () {
            return EmployeeMapper::CLASS;
        });

        $this->mapperRelations = new MapperRelations(
            EmployeeMapper::CLASS,
            $mapperLocator
        );
    }

    public function testSet_hasManyThrough_noThroughName()
    {
        $this->setExpectedException(
            Exception::CLASS,
            "Relation 'foo' does not exist"
        );

        $this->mapperRelations->set(
            'bar',
            HasManyThrough::CLASS,
            EmployeeMapper::CLASS,
            'foo'
        );
    }
}
