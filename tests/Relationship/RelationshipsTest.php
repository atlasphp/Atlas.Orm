<?php
namespace Atlas\Orm\Relationship;

use Atlas\Orm\Exception;
use Atlas\Orm\DataSource\Employee\EmployeeMapper;
use Atlas\Orm\DataSource\Employee\EmployeeRecord;
use Atlas\Orm\DataSource\Employee\EmployeeRecordSet;
use Atlas\Orm\DataSource\Employee\EmployeeRow;
use Atlas\Orm\Mapper\MapperLocator;

class RelationshipsTest extends \PHPUnit_Framework_TestCase
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

    public function testSet_manyToMany_noThroughName()
    {
        $this->setExpectedException(
            Exception::CLASS,
            "Relation 'foo' does not exist"
        );

        $this->relationships->set(
            EmployeeMapper::CLASS,
            'bar',
            'ManyToMany',
            EmployeeMapper::CLASS,
            'foo'
        );
    }

    public function testSet_noForeignMapper()
    {
        $this->setExpectedException(
            Exception::CLASS,
            "NoSuchMapper does not exist"
        );

        $this->relationships->set(
            EmployeeMapper::CLASS,
            'bar',
            'OneToOne',
            NoSuchMapper::CLASS
        );
    }
}
