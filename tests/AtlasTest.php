<?php
namespace Atlas\Orm;

use Atlas\Testing\DataSource\SqliteFixture;
use Atlas\Testing\DataSource\Employee\EmployeeMapper;
use Atlas\Testing\DataSource\Employee\EmployeeRecord;
use Atlas\Testing\DataSource\Employee\EmployeeRecordSet;
use Atlas\Mapper\MapperSelect;

class AtlasTest extends \PHPUnit\Framework\TestCase
{
    protected $atlas;

    protected $connection;

    public function setUp()
    {
        $this->connection = (new SqliteFixture())->exec();
        $this->atlas = Atlas::new($this->connection, Transaction::CLASS);
    }

    public function testMapper()
    {
        $this->assertInstanceOf(
            EmployeeMapper::CLASS,
            $this->atlas->mapper(EmployeeMapper::CLASS)
        );
    }

    public function testNewRecord()
    {
        $this->assertInstanceOf(
            EmployeeRecord::CLASS,
            $this->atlas->newRecord(EmployeeMapper::CLASS)
        );
    }

    public function testNewRecordSet()
    {
        $this->assertInstanceOf(
            EmployeeRecordSet::CLASS,
            $this->atlas->newRecordSet(EmployeeMapper::CLASS)
        );
    }

    public function testFetchRecord()
    {
        $this->assertInstanceOf(
            EmployeeRecord::CLASS,
            $this->atlas->fetchRecord(EmployeeMapper::CLASS, 1)
        );
    }

    public function testFetchRecordBy()
    {
        $this->assertInstanceOf(
            EmployeeRecord::CLASS,
            $this->atlas->fetchRecordBy(EmployeeMapper::CLASS, ['id' => 1])
        );
    }

    public function testFetchRecords()
    {
        $actual = $this->atlas->fetchRecords(EmployeeMapper::CLASS, [1, 2, 3]);
        $this->assertTrue(is_array($actual));
        $this->assertInstanceOf(EmployeeRecord::CLASS, $actual[0]);
        $this->assertInstanceOf(EmployeeRecord::CLASS, $actual[1]);
        $this->assertInstanceOf(EmployeeRecord::CLASS, $actual[2]);
    }

    public function testFetchRecordsBy()
    {
        $actual = $this->atlas->fetchRecordsBy(EmployeeMapper::CLASS, ['id' => [1, 2, 3]]);
        $this->assertTrue(is_array($actual));
        $this->assertInstanceOf(EmployeeRecord::CLASS, $actual[0]);
        $this->assertInstanceOf(EmployeeRecord::CLASS, $actual[1]);
        $this->assertInstanceOf(EmployeeRecord::CLASS, $actual[2]);
    }

    public function testFetchRecordSet()
    {
        $actual = $this->atlas->fetchRecordSet(EmployeeMapper::CLASS, [1, 2, 3]);
        $this->assertInstanceOf(EmployeeRecordSet::CLASS, $actual);
        $this->assertInstanceOf(EmployeeRecord::CLASS, $actual[0]);
        $this->assertInstanceOf(EmployeeRecord::CLASS, $actual[1]);
        $this->assertInstanceOf(EmployeeRecord::CLASS, $actual[2]);
    }

    public function testFetchRecordSetBy()
    {
        $actual = $this->atlas->fetchRecordSetBy(EmployeeMapper::CLASS, ['id' => [1, 2, 3]]);
        $this->assertInstanceOf(EmployeeRecordSet::CLASS, $actual);
        $this->assertInstanceOf(EmployeeRecord::CLASS, $actual[0]);
        $this->assertInstanceOf(EmployeeRecord::CLASS, $actual[1]);
        $this->assertInstanceOf(EmployeeRecord::CLASS, $actual[2]);
    }

    public function testSelect()
    {
        $this->assertInstanceOf(
            MapperSelect::CLASS,
            $this->atlas->select(EmployeeMapper::CLASS)
        );
    }

    public function testInsertUpdatePersistDelete()
    {
        $employee = $this->atlas->newRecord(EmployeeMapper::CLASS, [
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

    public function testTransaction() : void
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
}
