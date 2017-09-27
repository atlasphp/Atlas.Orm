<?php
namespace Atlas\Orm\Table;

use Atlas\Orm\Exception;
use Aura\Sql\ConnectionLocator;
use Aura\Sql\ExtendedPdo;

class ConnectionManagerTest extends \PHPUnit\Framework\TestCase
{
    protected $connectionLocator;

    protected $connectionManager;

    protected $conn;

    protected function setUp()
    {
        $conn = (object) [
            'default' => new ExtendedPdo('sqlite::memory:'),
            'read1' => new ExtendedPdo('sqlite::memory:'),
            'read2' => new ExtendedPdo('sqlite::memory:'),
            'write1' => new ExtendedPdo('sqlite::memory:'),
            'write2' => new ExtendedPdo('sqlite::memory:'),
        ];

        $this->conn = $conn;

        $this->connectionLocator = new ConnectionLocator();
        $this->connectionLocator->setDefault(function () use ($conn) { return $conn->default; });
        $this->connectionLocator->setRead('read1', function () use ($conn) { return $conn->read1; });
        $this->connectionLocator->setRead('read2', function () use ($conn) { return $conn->read2; });
        $this->connectionLocator->setWrite('write1', function () use ($conn) { return $conn->write1; });
        $this->connectionLocator->setWrite('write2', function () use ($conn) { return $conn->write2; });

        $this->connectionManager = new ConnectionManager($this->connectionLocator);
        $this->connectionManager->setRead('FooTable', 'read2');
        $this->connectionManager->setWrite('FooTable', 'write2');
    }

    public function testGetConnectionLocator()
    {
        $this->assertSame(
            $this->connectionLocator,
            $this->connectionManager->getConnectionLocator()
        );
    }

    public function testTransactionsOnDefault()
    {
        $connectionManager = new ConnectionManager(new ConnectionLocator(
            function () { return new ExtendedPdo('sqlite::memory:'); }
        ));

        $default = $connectionManager->getRead('FooTable');
        $this->assertFalse($default->inTransaction());

        $connectionManager->setReadTransactions();
        $this->assertFalse($default->inTransaction());

        $default = $connectionManager->getRead('FooTable');
        $this->assertTrue($default->inTransaction());
    }

    public function testReadTransactions()
    {
        $reada = $this->connectionManager->getRead('FooTable');
        $this->assertSame($this->conn->read2, $reada);
        $this->assertFalse($reada->inTransaction());

        // memoized connections do not start transactions
        $this->connectionManager->setReadTransactions();
        $this->assertFalse($reada->inTransaction());

        // only re-getting will start a transaction
        $readb = $this->connectionManager->getRead('FooTable');
        $this->assertSame($reada, $readb);
        $this->assertTrue($reada->inTransaction());
        $this->assertTrue($readb->inTransaction());

        // existing transactions do not get stopped
        $this->connectionManager->setReadTransactions(false);
        $this->assertTrue($reada->inTransaction());
        $this->assertTrue($readb->inTransaction());

        // only ending them will
        $this->connectionManager->rollBack();
        $this->assertFalse($reada->inTransaction());
        $this->assertFalse($readb->inTransaction());
    }

    public function testWriteTransactions()
    {
        // by default, for BC to 1.x, connection manager sets write transactions
        $this->connectionManager->setWriteTransactions(false);

        // now we can get started
        $writea = $this->connectionManager->getWrite('FooTable');
        $this->assertSame($this->conn->write2, $writea);
        $this->assertFalse($writea->inTransaction());

        // memoized connections do not start transactions
        $this->connectionManager->setWriteTransactions();
        $this->assertFalse($writea->inTransaction());

        // only re-getting will start a transaction
        $writeb = $this->connectionManager->getWrite('FooTable');
        $this->assertSame($writea, $writeb);
        $this->assertTrue($writea->inTransaction());
        $this->assertTrue($writeb->inTransaction());

        // existing transactions do not get stopped
        $this->connectionManager->setWriteTransactions(false);
        $this->assertTrue($writea->inTransaction());
        $this->assertTrue($writeb->inTransaction());

        // only ending them will
        $this->connectionManager->commit();
        $this->assertFalse($writea->inTransaction());
        $this->assertFalse($writeb->inTransaction());
    }

    public function testReadFromWrite_NEVER()
    {
        $this->assertSame('NEVER', $this->connectionManager->getReadFromWrite());

        $read = $this->connectionManager->getRead('FooTable');
        $this->assertSame($this->conn->read2, $read);

        $write = $this->connectionManager->getWrite('FooTable');
        $this->assertSame($this->conn->write2, $write);
    }

    public function testReadFromWrite_ALWAYS()
    {
        $this->connectionManager->setReadFromWrite('ALWAYS');
        $this->assertSame('ALWAYS', $this->connectionManager->getReadFromWrite());

        $read = $this->connectionManager->getRead('FooTable');
        $this->assertSame($this->conn->write2, $read);

        $write = $this->connectionManager->getWrite('FooTable');
        $this->assertSame($this->conn->write2, $write);
    }

    public function testReadFromWrite_WHILE_WRITING()
    {
        $this->connectionManager->setReadFromWrite('WHILE_WRITING');
        $this->assertSame('WHILE_WRITING', $this->connectionManager->getReadFromWrite());

        $read = $this->connectionManager->getRead('FooTable');
        $this->assertSame($this->conn->read2, $read);

        $write = $this->connectionManager->getWrite('FooTable');
        $this->assertSame($this->conn->write2, $write);

        $read = $this->connectionManager->getRead('FooTable');
        $this->assertSame($write, $read);

        // stop writing, should go back to read connection
        $this->connectionManager->rollBack();
        $read = $this->connectionManager->getRead('FooTable');
        $this->assertSame($this->conn->read2, $read);
    }

    public function testReadFromWrite_badOption()
    {
        $this->expectException(
            Exception::CLASS,
            "Expected one of 'ALWAYS', 'WHILE_WRITING', 'NEVER'; got 'foo' instead."
        );

        $this->connectionManager->setReadFromWrite('foo');
    }
}
