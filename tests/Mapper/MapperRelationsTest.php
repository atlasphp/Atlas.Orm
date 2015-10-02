<?php
namespace Atlas\Mapper;

use Atlas\Exception;
use Atlas\Fake\Employee\EmployeeMapper;
use Atlas\Fake\Employee\EmployeeRecord;
use Atlas\Fake\Employee\EmployeeRecordSet;
use Atlas\Fake\Employee\EmployeeRow;
use Atlas\Relationship\HasManyThrough;

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
