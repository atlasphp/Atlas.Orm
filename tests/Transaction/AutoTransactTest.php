<?php
namespace Atlas\Orm\Transaction;

use Atlas\Table\Exception;
use Atlas\Testing\DataSource\Employee\Employee;

class AutoTransactTest extends TransactionTest
{
    public function testRead()
    {
        $this->assertFalse($this->connection->inTransaction());
        $this->atlas->fetchRecord(Employee::CLASS, 1);
        $this->assertFalse($this->connection->inTransaction());
    }

    public function testWrite()
    {
        $this->assertFalse($this->connection->inTransaction());

        $employee = $this->atlas->fetchRecord(Employee::CLASS, 1);
        $employee->name = 'changed';
        $this->atlas->persist($employee);

        $this->assertFalse($this->connection->inTransaction());

        try {
            $employee->id = '999';
            $this->atlas->persist($employee);
        } catch (Exception $e) {
            $previous = $e->getPrevious();
            $this->assertSame(
                $previous->getMessage(),
                "Primary key value for 'id' changed"
            );
        }

        $this->assertFalse($this->connection->inTransaction());
    }
}
