<?php
namespace Atlas\Orm\Mapper;

use Atlas\Orm\Exception;
use Atlas\Orm\DataSource\Employee\EmployeeMapper;
use Atlas\Orm\DataSource\Employee\EmployeeRecord;
use Atlas\Orm\DataSource\Employee\EmployeeRecordSet;
use Atlas\Orm\DataSource\Employee\EmployeeRow;
use Atlas\Orm\Relation\ManyToMany;

class MapperRelationsTest extends \PHPUnit_Framework_TestCase
{
    protected $mapperLocator;
    protected $relations;

    protected function setUp()
    {
        $mapperLocator = new MapperLocator();
        $mapperLocator->set(EmployeeMapper::CLASS, function () {
            return EmployeeMapper::CLASS;
        });

        $this->relations = new FakeRelations($mapperLocator);
    }

    public function testSet_manyToMany_noThroughName()
    {
        $this->setExpectedException(
            Exception::CLASS,
            "Relation 'foo' does not exist"
        );

        $this->relations->set(
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

        $this->relations->set(
            EmployeeMapper::CLASS,
            'bar',
            'OneToOne',
            NoSuchMapper::CLASS
        );
    }
}
