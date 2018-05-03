<?php
namespace Atlas\Orm\Transaction;

use Atlas\Testing\DataSource\Employee\Employee;

class BeginOnReadTest extends TransactionTest
{
    public function testRead()
    {
        $this->assertFalse($this->connection->inTransaction());
        $this->atlas->fetchRecord(Employee::CLASS, 1);
        $this->assertTrue($this->connection->inTransaction());
    }

    public function testWrite()
    {
        $this->assertFalse($this->connection->inTransaction());
        $employee = $this->atlas->fetchRecord(Employee::CLASS, 1);
        $employee->name = 'changed';
        $this->atlas->persist($employee);
        $this->assertTrue($this->connection->inTransaction());
    }
}
