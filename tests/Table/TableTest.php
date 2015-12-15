<?php
namespace Atlas\Orm\Table;

use Atlas\Orm\Assertions;
use Atlas\Orm\DataSource\Employee\EmployeeTableGateway;
use Atlas\Orm\DataSource\Employee\EmployeeTableEvents;
use Atlas\Orm\SqliteFixture;
use Aura\Sql\ConnectionLocator;
use Aura\Sql\ExtendedPdo;
use Aura\SqlQuery\QueryFactory;
use InvalidArgumentException;
use UnexpectedValueException;

class TableTest extends \PHPUnit_Framework_TestCase
{
    use Assertions;

    protected $table;

    protected function setUp()
    {
        parent::setUp();

        $connectionLocator = new ConnectionLocator(function () {
            return new ExtendedPdo('sqlite::memory:');
        });

        $this->table = new EmployeeTableGateway(
            $connectionLocator,
            new QueryFactory('sqlite'),
            new IdentityMap(),
            new EmployeeTableEvents()
        );

        $fixture = new SqliteFixture($this->table->getWriteConnection());
        $fixture->exec();
    }

    public function testGetIdentityMap()
    {
        $this->assertInstanceOf(
            'Atlas\Orm\Table\IdentityMap',
            $this->table->getIdentityMap()
        );
    }

    public function testFetchRow()
    {
        $expect = [
            'id' => '1',
            'name' => 'Anna',
            'building' => '1',
            'floor' => '1',
        ];
        $actual = $this->table->fetchRow(1);
        $this->assertInstanceOf(Row::CLASS, $actual);
        $this->assertSame($expect, $actual->getArrayCopy());

        $again = $this->table->fetchRow(1);
        $this->assertInstanceOf(Row::CLASS, $again);
        $this->assertSame($again, $actual);

        $actual = $this->table->fetchRow(-1);
        $this->assertFalse($actual);
    }

    public function testFetchRowBy()
    {
        $expect = [
            'id' => '1',
            'name' => 'Anna',
            'building' => '1',
            'floor' => '1',
        ];
        $actual = $this->table->fetchRowBy(['id' => 1]);
        $this->assertInstanceOf(Row::CLASS, $actual);
        $this->assertSame($expect, $actual->getArrayCopy());

        $again = $this->table->fetchRowBy(['id' => 1]);
        $this->assertInstanceOf(Row::CLASS, $again);
        $this->assertSame($again, $actual);

        $actual = $this->table->fetchRowBy(['id' => -1]);
        $this->assertFalse($actual);
    }

    public function testFetchRowSet()
    {
        $this->assertSame([], $this->table->fetchRowSet([]));

        $expect = [
            [
                'id' => '1',
                'name' => 'Anna',
                'building' => '1',
                'floor' => '1',
            ],
            [
                'id' => '2',
                'name' => 'Betty',
                'building' => '1',
                'floor' => '2',
            ],
            [
                'id' => '3',
                'name' => 'Clara',
                'building' => '1',
                'floor' => '3',
            ],
        ];

        $actual = $this->table->fetchRowSet([1, 2, 3]);
        $this->assertInstanceOf(RowSet::CLASS, $actual);
        $this->assertCount(3, $actual);
        $this->assertSame($expect[0], $actual[0]->getArrayCopy());
        $this->assertSame($expect[1], $actual[1]->getArrayCopy());
        $this->assertSame($expect[2], $actual[2]->getArrayCopy());

        $again = $this->table->fetchRowSet([1, 2, 3]);
        $this->assertInstanceOf(RowSet::CLASS, $again);
        $this->assertCount(3, $again);
        $this->assertSame($actual[0], $again[0]);
        $this->assertSame($actual[1], $again[1]);
        $this->assertSame($actual[2], $again[2]);

        $actual = $this->table->fetchRowSet([997, 998, 999]);
        $this->assertSame(array(), $actual);
    }

    public function testFetchRowSetBy()
    {
        $expect = [
            [
                'id' => '1',
                'name' => 'Anna',
                'building' => '1',
                'floor' => '1',
            ],
            [
                'id' => '2',
                'name' => 'Betty',
                'building' => '1',
                'floor' => '2',
            ],
            [
                'id' => '3',
                'name' => 'Clara',
                'building' => '1',
                'floor' => '3',
            ],
        ];

        $actual = $this->table->fetchRowSetBy(['id' => [1, 2, 3]]);
        $this->assertInstanceOf(RowSet::CLASS, $actual);
        $this->assertCount(3, $actual);
        $this->assertSame($expect[0], $actual[0]->getArrayCopy());
        $this->assertSame($expect[1], $actual[1]->getArrayCopy());
        $this->assertSame($expect[2], $actual[2]->getArrayCopy());

        $again = $this->table->fetchRowSetBy(['id' => [1, 2, 3]]);
        $this->assertInstanceOf(RowSet::CLASS, $again);
        $this->assertCount(3, $again);
        $this->assertSame($actual[0], $again[0]);
        $this->assertSame($actual[1], $again[1]);
        $this->assertSame($actual[2], $again[2]);

        $actual = $this->table->fetchRowSetBy(['id' => [997, 998, 999]]);
        $this->assertSame(array(), $actual);
    }

    public function testInsert()
    {
        $row = $this->table->newRow([
            'id' => null,
            'name' => 'Mona',
            'building' => '10',
            'floor' => '99',
        ]);

        // does the insert *look* successful?
        $success = $this->table->insert($row);
        $this->assertTrue($success);

        // did the autoincrement ID get retained?
        $this->assertEquals(13, $row->id);

        // did it save in the identity map?
        $again = $this->table->fetchRow(13);
        $this->assertSame($row, $again);

        // was it *actually* inserted?
        $expect = [
            'id' => '13',
            'name' => 'Mona',
            'building' => '10',
            'floor' => '99',
        ];
        $actual = $this->table->getReadConnection()->fetchOne(
            'SELECT * FROM employee WHERE id = 13'
        );
        $this->assertSame($expect, $actual);

        // try to insert again, should fail on unique name
        $this->silenceErrors();
        $this->setExpectedException(
            UnexpectedValueException::CLASS,
            "Expected 1 row affected, actual 0"
        );
        $this->table->insert($row);

        // DOES THIS ACTUALLY RUN???

        // try to insert a row of the wrong type
        $row = new Row('FakeTable', new RowIdentity(['id' => null]), []);
        $this->setExpectedException(
            UnexpectedValueException::CLASS,
            "Expected Row with table class 'Atlas\Orm\DataSource\Employee\EmployeeTableGateway', had 'FakeTable' instead."
        );
        $this->table->insert($row);
    }

    public function testUpdate()
    {
        // fetch a row, then modify and update it
        $row = $this->table->fetchRowBy(['name' => 'Anna']);
        $row->name = 'Annabelle';

        // did the update *look* successful?
        $success = $this->table->update($row);
        $this->assertTrue($success);

        // is it still in the identity map?
        $again = $this->table->fetchRowBy(['name' => 'Annabelle']);
        $this->assertSame($row, $again);

        // was it *actually* updated?
        $expect = $row->getArrayCopy();
        $actual = $this->table->getReadConnection()->fetchOne(
            "SELECT * FROM employee WHERE name = 'Annabelle'"
        );
        $this->assertSame($expect, $actual);

        // try to update again, should be a no-op because there are no changes
        $this->assertFalse($this->table->update($row));

        // // delete the row and try to update it, should fail
        // $this->assertTrue($this->table->delete($row));
        // $row->name = 'Foo';
        // $this->setExpectedException(
        //     UnexpectedValueException::CLASS,
        //     "Expected 1 row affected, actual 0"
        // );
        // $this->table->update($row);

        // try to update a row from the wrong table
        $row = new Row('FakeTable', new RowIdentity(['id' => null]), []);
        $this->setExpectedException(
            UnexpectedValueException::CLASS,
            "Expected Row with table class 'Atlas\Orm\DataSource\Employee\EmployeeTableGateway', got 'FakeTable' instead."
        );
        $this->table->update($row);
    }

    public function testDelete()
    {
        // fetch a row, then delete it
        $row = $this->table->fetchRowBy(['name' => 'Anna']);
        $this->assertTrue($this->table->delete($row));

        // did it delete?
        $actual = $this->table->fetchRowBy(['name' => 'Anna']);
        $this->assertFalse($actual);

        // do we still have everything else?
        $actual = $this->table->select()->where('id > 0')->fetchRowSet();
        $expect = 11;
        $this->assertEquals($expect, count($actual));

        // try to delete a row of the wrong type
        $row = new Row('FakeTable', new RowIdentity(['id' => null]), []);
        $this->setExpectedException(
            UnexpectedValueException::CLASS,
            "Expected Row with table class 'Atlas\Orm\DataSource\Employee\EmployeeTableGateway', got 'FakeTable' instead."
        );
        $this->table->delete($row);
    }

    protected function silenceErrors()
    {
        $conn = $this->table->getWriteConnection();
        $conn->setAttribute($conn::ATTR_ERRMODE, $conn::ERRMODE_SILENT);
    }

    public function testSelectWhereNull()
    {
        $select = $this->table->select(['name' => null])->cols(['id']);

        $expect = '
            SELECT
                id
            FROM
                "employee"
            WHERE
                "employee"."name" IS NULL
        ';

        $actual = $select->__toString();
        $this->assertSameSql($expect, $actual);
    }

    public function testSave()
    {
        // insert
        $row = $this->table->newRow([
            'id' => null,
            'name' => 'Mona',
            'building' => '10',
            'floor' => '99',
        ]);
        $this->assertTrue($this->table->save($row));
        $this->assertTrue($row->isSaved());

        // update
        $row = $this->table->fetchRowBy(['name' => 'Mona']);
        $row->name = 'Mona Lisa';
        $this->assertTrue($this->table->save($row));
        $this->assertTrue($row->isSaved());

        // delete
        $row = $this->table->fetchRowBy(['name' => 'Mona Lisa']);
        $row->markAsTrash();
        $this->assertTrue($this->table->delete($row));
        $this->assertTrue($row->isDeleted());
    }
}
