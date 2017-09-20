<?php
namespace Atlas\Orm;

use Atlas\Orm\DataSource\Employee\EmployeeMapper;
use Atlas\Orm\Mapper\Record;
use Aura\Sql\ExtendedPdo;
use PDOException;

class TransactionTest extends \PHPUnit\Framework\TestCase
{
    protected $transaction;
    protected $mapperLocator;

    protected function setUp()
    {
        $atlasContainer = new AtlasContainer('sqlite::memory:');

        $atlasContainer->setMappers([
            EmployeeMapper::CLASS,
        ]);

        $connection = $atlasContainer->getConnectionLocator()->getDefault();
        $fixture = new SqliteFixture($connection);
        $fixture->exec();

        $this->atlas = $atlasContainer->getAtlas();
    }

    public function testInsert()
    {
        // create the record to insert
        $mapper = $this->atlas->mapper(EmployeeMapper::CLASS);
        $employee = $mapper->newRecord([
            'name' => 'Mona',
            'building' => 10,
            'floor' => 99,
        ]);

        // insert as part of the transaction plan
        $transaction = $this->atlas->newTransaction();
        $transaction->insert($employee);

        // get the transaction plan
        $plan = $transaction->getPlan();

        // should be only one work item
        $this->assertSame(1, count($plan));

        // test the work item
        $work = $plan[0];
        $expect = "insert " . Record::CLASS . " via " . EmployeeMapper::CLASS;
        $this->assertSame($expect, $work->getLabel());
        $this->assertSame([$mapper, 'insert'], $work->getCallable());
        $this->assertSame($employee, $work->getRecord());
        $this->assertFalse($work->getInvoked());
        $this->assertNull($work->getResult());

        // execute the transaction
        $result = $transaction->exec();
        $this->assertTrue($result);

        // did the work appear to go right?
        $this->assertTrue($work->getInvoked());
        $this->assertTrue($work->getResult());
        $this->assertSame('13', $employee->id);

        // completed work should be the same as the planned work, with no failures
        $this->assertSame($transaction->getPlan(), $transaction->getCompleted());
        $this->assertNull($transaction->getFailure());
        $this->assertNull($transaction->getException());

        // did the insert actually occur?
        $expect = ['id' => '13', 'name' => 'Mona', 'building' => '10', 'floor' => '99'];
        $actual = $mapper->select()->cols(['*'])->where('id = 13')->fetchOne();
        $this->assertSame($expect, $actual);
    }

    public function testUpdate()
    {
        $mapper = $this->atlas->mapper(EmployeeMapper::CLASS);
        $employee = $mapper->fetchRecordBy(['name' => 'Anna']);
        $employee->name = 'Annabelle';

        // add update to the transaction plan
        $transaction = $this->atlas->newTransaction();
        $transaction->update($employee);

        // get the transaction plan
        $plan = $transaction->getPlan();

        // should be only one work item
        $this->assertSame(1, count($plan));

        // test the work item
        $work = $plan[0];
        $expect = "update " . Record::CLASS . " via " . EmployeeMapper::CLASS;
        $this->assertSame($expect, $work->getLabel());
        $this->assertSame([$mapper, 'update'], $work->getCallable());
        $this->assertSame($employee, $work->getRecord());
        $this->assertFalse($work->getInvoked());
        $this->assertNull($work->getResult());

        // execute the transaction
        $result = $transaction->exec();
        $this->assertTrue($result);

        // did the work appear to go right?
        $this->assertTrue($work->getInvoked());
        $this->assertTrue($work->getResult());

        // completed work should be the same as the planned work, with no failures
        $this->assertSame($transaction->getPlan(), $transaction->getCompleted());
        $this->assertNull($transaction->getFailure());
        $this->assertNull($transaction->getException());

        // did the update actually occur?
        $expect = ['id' => '1', 'name' => 'Annabelle', 'building' => '1', 'floor' => '1'];
        $actual = $mapper->select()->cols(['*'])->where('id = 1')->fetchOne();
        $this->assertSame($expect, $actual);
    }

    public function testDelete()
    {
        $mapper = $this->atlas->mapper(EmployeeMapper::CLASS);
        $employee = $mapper->fetchRecordBy(['name' => 'Anna']);

        // add delete to the transaction plan
        $transaction = $this->atlas->newTransaction();
        $transaction->delete($employee);

        // get the transaction plan
        $plan = $transaction->getPlan();

        // should be only one work item
        $this->assertSame(1, count($plan));

        // test the work item
        $work = $plan[0];
        $expect = "delete " . Record::CLASS . " via " . EmployeeMapper::CLASS;
        $this->assertSame($expect, $work->getLabel());
        $this->assertSame([$mapper, 'delete'], $work->getCallable());
        $this->assertSame($employee, $work->getRecord());
        $this->assertFalse($work->getInvoked());
        $this->assertNull($work->getResult());

        // execute the transaction
        $result = $transaction->exec();
        $this->assertTrue($result);

        // did the work appear to go right?
        $this->assertTrue($work->getInvoked());
        $this->assertTrue($work->getResult());

        // completed work should be the same as the planned work, with no failures
        $this->assertSame($transaction->getPlan(), $transaction->getCompleted());
        $this->assertNull($transaction->getFailure());
        $this->assertNull($transaction->getException());

        // did the delete actually occur?
        $actual = $mapper->select(['name' => 'Anna'])->cols(['*'])->fetchOne();
        $this->assertNull($actual);
    }

    public function testExec_reExec()
    {
        $mapper = $this->atlas->mapper(EmployeeMapper::CLASS);
        $employee = $mapper->fetchRecordBy(['name' => 'Anna']);

        // add delete to the transaction plan
        $transaction = $this->atlas->newTransaction();
        $transaction->delete($employee);

        // execute the transaction
        $result = $transaction->exec();
        $this->assertTrue($result);

        // try it again, should fail
        $this->expectException(
            'Atlas\Orm\Exception',
            'Cannot re-execute a prior transaction.'
        );
        $transaction->exec();
    }

    public function testExec_rollBack()
    {
        $transaction = $this->atlas->newTransaction();
        $mapper = $this->atlas->mapper(EmployeeMapper::CLASS);

        // create a bad record for insertion
        $transaction->insert($mapper->newRecord([
            'name' => null,
            'building' => -1,
            'floor' => -1,
        ]));

        // create a good record, but should not get this far
        $transaction->insert($mapper->newRecord([
            'name' => 'Mona',
            'building' => 10,
            'floor' => 99,
        ]));

        // transaction should fail
        $result = $transaction->exec();
        $this->assertFalse($result);

        // should have failed on the first (number zero) insertion via PDO
        $this->assertInstanceOf(PDOException::CLASS, $transaction->getException());
        $actual = $transaction->getFailure();
        $expect = $transaction->getPlan()[0];
        $this->assertSame($expect, $actual);
    }
}
