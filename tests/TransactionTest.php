<?php
namespace Atlas;

use Atlas\DataSource\Employee\EmployeeMapper;
use Atlas\DataSource\Employee\EmployeeRecord;
use Aura\Sql\ExtendedPdo;

class TransactionTest extends \PHPUnit_Framework_TestCase
{
    protected $transaction;
    protected $mapperLocator;

    protected function setUp()
    {
        $atlasContainer = new AtlasContainer('sqlite');
        $atlasContainer->setDefaultConnection(function () {
            return new ExtendedPdo('sqlite::memory:');
        });
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
        $employee = $mapper->newRecord();
        $employee->name = 'Mona';
        $employee->building = 10;
        $employee->floor = 99;

        // insert as part of the transaction plan
        $transaction = $this->atlas->newTransaction();
        $transaction->insert($employee);

        // get the transaction plan
        $plan = $transaction->getPlan();

        // should be only one work item
        $this->assertSame(1, count($plan));

        // test the work item
        $work = $plan[0];
        $this->assertSame("insert " . EmployeeRecord::CLASS, $work->getLabel());
        $this->assertSame([$mapper, 'insert'], $work->getCallable());
        $args = $work->getArgs();
        $this->assertSame($employee, $args[0]);
        $this->assertFalse($work->getInvoked());
        $this->assertNull($work->getResult());

        // execute the transaction
        $result = $transaction->exec();
        $this->assertTrue($result);

        // did the work appear to go right?
        $this->assertTrue($work->getInvoked());
        $this->assertTrue($work->getResult());
        $this->assertSame('13', $employee->id);

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
        $this->assertSame("update " . EmployeeRecord::CLASS, $work->getLabel());
        $this->assertSame([$mapper, 'update'], $work->getCallable());
        $args = $work->getArgs();
        $this->assertSame($employee, $args[0]);
        $this->assertFalse($work->getInvoked());
        $this->assertNull($work->getResult());

        // execute the transaction
        $result = $transaction->exec();
        $this->assertTrue($result);

        // did the work appear to go right?
        $this->assertTrue($work->getInvoked());
        $this->assertTrue($work->getResult());

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
        $this->assertSame("delete " . EmployeeRecord::CLASS, $work->getLabel());
        $this->assertSame([$mapper, 'delete'], $work->getCallable());
        $args = $work->getArgs();
        $this->assertSame($employee, $args[0]);
        $this->assertFalse($work->getInvoked());
        $this->assertNull($work->getResult());

        // execute the transaction
        $result = $transaction->exec();
        $this->assertTrue($result);

        // did the work appear to go right?
        $this->assertTrue($work->getInvoked());
        $this->assertTrue($work->getResult());

        // did the delete actually occur?
        $actual = $mapper->select(['name' => 'Anna'])->cols(['*'])->fetchOne();
        $this->assertFalse($actual);
    }
}
