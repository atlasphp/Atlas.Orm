<?php
namespace Atlas\Orm\Transaction;

use Atlas\Orm\Atlas;
use Atlas\Testing\DataSource\Employee\Employee;
use Atlas\Testing\DataSource\Employee\EmployeeRecord;
use Atlas\Testing\DataSource\Employee\EmployeeRecordSet;
use Atlas\Testing\DataSourceFixture;

abstract class TransactionTest extends \PHPUnit\Framework\TestCase
{
    protected $atlas;

    protected $connection;

    public function setUp()
    {
        $this->connection = (new DataSourceFixture())->exec();
        $transactionClass = substr(static::class, 0, -4);
        $this->atlas = Atlas::new($this->connection, $transactionClass);
    }

    abstract public function testRead();

    abstract public function testWrite();
}
