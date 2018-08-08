<?php
namespace Atlas\Orm;

use Atlas\Testing\DataSource\Employee\Employee;
use Atlas\Testing\DataSource\Employee\EmployeeRecord;
use Atlas\Testing\DataSource\Employee\EmployeeRecordSet;
use Atlas\Testing\DataSource\Employee\EmployeeSelect;
use Atlas\Testing\DataSourceFixture;

class AtlasTest extends \PHPUnit\Framework\TestCase
{
    protected $atlas;

    protected $connection;

    public function setUp()
    {
        $this->connection = (new DataSourceFixture())->exec();
        $this->atlas = Atlas::new($this->connection);
    }

    public function testMapper()
    {
        $this->assertInstanceOf(
            Employee::CLASS,
            $this->atlas->mapper(Employee::CLASS)
        );
    }

    public function testNewRecord()
    {
        $this->assertInstanceOf(
            EmployeeRecord::CLASS,
            $this->atlas->newRecord(Employee::CLASS)
        );
    }

    public function testNewRecords()
    {
        $actual = $this->atlas->newRecords(Employee::CLASS, [
            [
                'name' => 'foo'
            ],
            [
                'name' => 'bar'
            ],
        ]);

        $this->assertInstanceOf(EmployeeRecord::CLASS, $actual[0]);
        $this->assertInstanceOf(EmployeeRecord::CLASS, $actual[1]);
    }

    public function testNewRecordSet()
    {
        $this->assertInstanceOf(
            EmployeeRecordSet::CLASS,
            $this->atlas->newRecordSet(Employee::CLASS)
        );
    }

    public function testFetchRecord()
    {
        $this->assertInstanceOf(
            EmployeeRecord::CLASS,
            $this->atlas->fetchRecord(Employee::CLASS, 1)
        );
    }

    public function testFetchRecordBy()
    {
        $this->assertInstanceOf(
            EmployeeRecord::CLASS,
            $this->atlas->fetchRecordBy(Employee::CLASS, ['id' => 1])
        );
    }

    public function testFetchRecords()
    {
        $actual = $this->atlas->fetchRecords(Employee::CLASS, [1, 2, 3]);
        $this->assertTrue(is_array($actual));
        $this->assertInstanceOf(EmployeeRecord::CLASS, $actual[0]);
        $this->assertInstanceOf(EmployeeRecord::CLASS, $actual[1]);
        $this->assertInstanceOf(EmployeeRecord::CLASS, $actual[2]);
    }

    public function testFetchRecordsBy()
    {
        $actual = $this->atlas->fetchRecordsBy(Employee::CLASS, ['id' => [1, 2, 3]]);
        $this->assertTrue(is_array($actual));
        $this->assertInstanceOf(EmployeeRecord::CLASS, $actual[0]);
        $this->assertInstanceOf(EmployeeRecord::CLASS, $actual[1]);
        $this->assertInstanceOf(EmployeeRecord::CLASS, $actual[2]);
    }

    public function testFetchRecordSet()
    {
        $actual = $this->atlas->fetchRecordSet(Employee::CLASS, [1, 2, 3]);
        $this->assertInstanceOf(EmployeeRecordSet::CLASS, $actual);
        $this->assertInstanceOf(EmployeeRecord::CLASS, $actual[0]);
        $this->assertInstanceOf(EmployeeRecord::CLASS, $actual[1]);
        $this->assertInstanceOf(EmployeeRecord::CLASS, $actual[2]);
    }

    public function testFetchRecordSetBy()
    {
        $actual = $this->atlas->fetchRecordSetBy(Employee::CLASS, ['id' => [1, 2, 3]]);
        $this->assertInstanceOf(EmployeeRecordSet::CLASS, $actual);
        $this->assertInstanceOf(EmployeeRecord::CLASS, $actual[0]);
        $this->assertInstanceOf(EmployeeRecord::CLASS, $actual[1]);
        $this->assertInstanceOf(EmployeeRecord::CLASS, $actual[2]);
    }

    public function testSelect()
    {
        $this->assertInstanceOf(
            EmployeeSelect::CLASS,
            $this->atlas->select(Employee::CLASS)
        );
    }

    public function testInsertUpdatePersistDelete()
    {
        $employee = $this->atlas->newRecord(Employee::CLASS, [
            'name' => 'Foo'
        ]);

        $this->atlas->insert($employee);

        $employee->name = 'Bar';
        $this->atlas->update($employee);

        $employee->name = 'Baz';
        $this->atlas->persist($employee);

        $this->atlas->delete($employee);

        $this->assertTrue(true); // no exceptions means all is well
    }

    public function testPersistRecords()
    {
        $employees = $this->atlas->fetchRecords(Employee::CLASS, [1, 2, 3, 4, 5]);
        foreach ($employees as $employee) {
            $employee->name .= 'changed';
        }
        $employees[3]->setDelete();
        $employees[4]->setDelete();
        $this->atlas->persistRecords($employees);
        $this->assertSame('DELETED', $employees[3]->getRow()->getStatus());
        $this->assertSame('DELETED', $employees[4]->getRow()->getStatus());
    }

    public function testPersistRecordSet()
    {
        $employees = $this->atlas->fetchRecordSet(Employee::CLASS, [1, 2, 3, 4, 5]);
        foreach ($employees as $employee) {
            $employee->name .= 'changed';
        }
        $employees[3]->setDelete();
        $employees[4]->setDelete();
        $actual = $this->atlas->persistRecordSet($employees);
        $this->assertSame('DELETED', $employees[3]->getRow()->getStatus());
        $this->assertSame('DELETED', $employees[4]->getRow()->getStatus());
    }

    public function testTransaction()
    {
        $this->assertFalse($this->connection->inTransaction());

        $this->atlas->beginTransaction();
        $this->assertTrue($this->connection->inTransaction());
        $this->atlas->commit();
        $this->assertFalse($this->connection->inTransaction());

        $this->atlas->beginTransaction();
        $this->assertTrue($this->connection->inTransaction());
        $this->atlas->rollBack();
        $this->assertFalse($this->connection->inTransaction());
    }

    public function testQueryLogging()
    {
        $this->assertEmpty($this->atlas->getQueries());
        $this->atlas->logQueries();
        $this->atlas->fetchRecords(Employee::CLASS, [1, 2, 3]);

        $queries = $this->atlas->getQueries();
        $this->assertCount(1, $queries);

        $actual = $queries[0];
        $this->assertSame('DEFAULT', $actual['connection']);
        $this->assertTrue($actual['start'] > 0);
        $this->assertTrue($actual['finish'] > $actual['start']);
        $this->assertTrue($actual['duration'] > 0);
        $this->assertTrue($actual['statement'] !== '');
        $this->assertTrue($actual['trace'] !== '');
    }
}
