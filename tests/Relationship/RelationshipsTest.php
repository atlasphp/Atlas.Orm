<?php
namespace Atlas\Orm\Relationship;

use Atlas\Orm\Exception;
use Atlas\Orm\DataSource\Employee\EmployeeMapper;
use Atlas\Orm\DataSource\Employee\EmployeeRecord;
use Atlas\Orm\DataSource\Employee\EmployeeRecordSet;
use Atlas\Orm\DataSource\Employee\EmployeeRow;
use Atlas\Orm\Mapper\MapperLocator;

class RelationshipsTest extends \PHPUnit\Framework\TestCase
{
    protected $mapperLocator;
    protected $relations;

    protected function setUp()
    {
        $mapperLocator = new MapperLocator();
        $mapperLocator->set(EmployeeMapper::CLASS, function () {
            return EmployeeMapper::CLASS;
        });

        $this->relationships = new FakeRelationships($mapperLocator);
    }

    public function testManyToMany_noThroughName()
    {
        $this->expectException(
            Exception::CLASS,
            "Relationship 'foo' does not exist."
        );

        $this->relationships->manyToMany(
            'bar',
            EmployeeMapper::CLASS,
            EmployeeMapper::CLASS,
            'foo'
        );
    }

    public function test_noForeignMapper()
    {
        $this->expectException(
            Exception::CLASS,
            "NoSuchMapper does not exist"
        );

        $this->relationships->oneToOne(
            'bar',
            EmployeeMapper::CLASS,
            NoSuchMapper::CLASS
        );
    }
}
