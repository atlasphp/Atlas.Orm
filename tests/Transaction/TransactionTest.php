<?php
namespace Atlas\Orm\Transaction;

use Atlas\Testing\DataSource\SqliteFixture;
use Atlas\Testing\DataSource\Employee\EmployeeMapper;
use Atlas\Testing\DataSource\Employee\EmployeeRecord;
use Atlas\Testing\DataSource\Employee\EmployeeRecordSet;
use Atlas\Mapper\MapperSelect;
use Atlas\Orm\Atlas;

abstract class TransactionTest extends \PHPUnit\Framework\TestCase
{
    protected $atlas;

    protected $connection;

    public function setUp()
    {
        $this->connection = (new SqliteFixture())->exec();
        $transactionClass = substr(static::class, 0, -4);
        $this->atlas = Atlas::new($this->connection, $transactionClass);
    }

    abstract public function testRead();

    abstract public function testWrite();
}
