<?php
namespace Atlas\Orm\Mapper;

use Atlas\Orm\DataSource\Auto\AutoMapper;
use Atlas\Orm\DataSource\Auto\AutoTable;
use Atlas\Orm\DataSource\Employee\EmployeeMapper;
use Atlas\Orm\DataSource\Employee\EmployeeRecordFactory;
use Atlas\Orm\DataSource\Employee\EmployeeRecordFilter;
use Atlas\Orm\DataSource\Employee\EmployeeRelations;
use Atlas\Orm\DataSource\Employee\EmployeeRowFactory;
use Atlas\Orm\DataSource\Employee\EmployeeRowFilter;
use Atlas\Orm\DataSource\Employee\EmployeeTable;
use Atlas\Orm\Mapper\MapperRelations;
use Atlas\Orm\Mapper\RecordFactory;
use Atlas\Orm\SqliteFixture;
use Atlas\Orm\Table\FakeRow;
use Atlas\Orm\Table\FakeRowIdentity;
use Atlas\Orm\Table\IdentityMap;
use Aura\Sql\ConnectionLocator;
use Aura\Sql\ExtendedPdo;
use Aura\SqlQuery\QueryFactory;
use InvalidArgumentException;
use UnexpectedValueException;

class MapperTest extends \PHPUnit_Framework_TestCase
{
    protected $table;
    protected $mapper;

    protected function setUp()
    {
        parent::setUp();

        $connectionLocator = new ConnectionLocator(function () {
            return new ExtendedPdo('sqlite::memory:');
        });

        $this->table = new EmployeeTable(
            $connectionLocator,
            new QueryFactory('sqlite'),
            new IdentityMap(),
            new EmployeeRowFactory(),
            new EmployeeRowFilter()
        );

        $fixture = new SqliteFixture($this->table->getWriteConnection());
        $fixture->exec();

        $this->mapper = new EmployeeMapper(
            $this->table,
            new EmployeeRecordFactory(),
            new EmployeeRecordFilter(),
            new EmployeeRelations(new MapperLocator())
        );
    }

    public function testGetTable()
    {
        $this->assertSame($this->table, $this->mapper->getTable());
    }

    public function testGetRelations()
    {
        $this->assertInstanceOf(EmployeeRelations::CLASS, $this->mapper->getRelations());
    }

    public function testFetchRecord()
    {
        $expect = [
            'id' => '1',
            'name' => 'Anna',
            'building' => '1',
            'floor' => '1',
        ];

        // fetch success
        $record1 = $this->mapper->fetchRecord(1);
        $this->assertInstanceOf(Record::CLASS, $record1);
        $row1 = $record1->getRow();
        $this->assertSame($expect, $row1->getArrayCopy());

        // fetch again
        $record2 = $this->mapper->fetchRecord(1);
        $this->assertInstanceOf(Record::CLASS, $record2);
        $this->assertNotSame($record1, $record2);
        $row2 = $record2->getRow();
        $this->assertSame($row1, $row2);

        // fetch failure
        $actual = $this->mapper->fetchRecord(-1);
        $this->assertFalse($actual);
    }

    public function testFetchRecordBy()
    {
        $expect = [
            'id' => '1',
            'name' => 'Anna',
            'building' => '1',
            'floor' => '1',
        ];

        // fetch success
        $record1 = $this->mapper->fetchRecordBy(['id' => '1']);
        $this->assertInstanceOf(Record::CLASS, $record1);
        $row1 = $record1->getRow();
        $this->assertSame($expect, $row1->getArrayCopy());

        // fetch again
        $record2 = $this->mapper->fetchRecordBy(['id' => '1']);
        $this->assertInstanceOf(Record::CLASS, $record2);
        $this->assertNotSame($record1, $record2);
        $row2 = $record2->getRow();
        $this->assertSame($row1, $row2);

        // fetch failure
        $actual = $this->mapper->fetchRecordBy(['id' => '-1']);
        $this->assertFalse($actual);
    }

    public function testSelectFetchRecord()
    {
        $expect = [
            'id' => '1',
            'name' => 'Anna',
            'building' => '1',
            'floor' => '1',
        ];

        // fetch success
        $select = $this->mapper->select(['id' => '1']);
        $record1 = $select->fetchRecord();
        $this->assertInstanceOf(Record::CLASS, $record1);
        $row1 = $record1->getRow();
        $this->assertSame($expect, $row1->getArrayCopy());

        // fetch again
        $record2 = $select->fetchRecord();
        $this->assertInstanceOf(Record::CLASS, $record2);
        $this->assertNotSame($record1, $record2);
        $row2 = $record2->getRow();
        $this->assertSame($row1, $row2);

        // fetch failure
        $select = $this->mapper->select(['id' => '-1']);
        $actual = $select->fetchRecord();
        $this->assertFalse($actual);
    }

    public function testFetchRecordSet()
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

        $actual = $this->mapper->fetchRecordSet([1, 2, 3]);
        $this->assertInstanceOf(RecordSet::CLASS, $actual);
        $this->assertCount(3, $actual);
        $this->assertInstanceOf(Record::CLASS, $actual[0]);
        $this->assertInstanceOf(Record::CLASS, $actual[1]);
        $this->assertInstanceOf(Record::CLASS, $actual[2]);
        $this->assertSame($expect[0], $actual[0]->getRow()->getArrayCopy());
        $this->assertSame($expect[1], $actual[1]->getRow()->getArrayCopy());
        $this->assertSame($expect[2], $actual[2]->getRow()->getArrayCopy());

        $again = $this->mapper->fetchRecordSet([1, 2, 3]);
        $this->assertInstanceOf(RecordSet::CLASS, $again);
        $this->assertCount(3, $again);
        $this->assertInstanceOf(Record::CLASS, $again[0]);
        $this->assertInstanceOf(Record::CLASS, $again[1]);
        $this->assertInstanceOf(Record::CLASS, $again[2]);
        $this->assertSame($actual[0]->getRow(), $again[0]->getRow());
        $this->assertSame($actual[1]->getRow(), $again[1]->getRow());
        $this->assertSame($actual[2]->getRow(), $again[2]->getRow());

        $actual = $this->mapper->fetchRecordSet([997, 998, 999]);
        $this->assertSame(array(), $actual);
    }

    public function testFetchRecordSetBy()
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

        $actual = $this->mapper->fetchRecordSetBy(['id' => [1, 2, 3]]);
        $this->assertInstanceOf(RecordSet::CLASS, $actual);
        $this->assertCount(3, $actual);
        $this->assertInstanceOf(Record::CLASS, $actual[0]);
        $this->assertInstanceOf(Record::CLASS, $actual[1]);
        $this->assertInstanceOf(Record::CLASS, $actual[2]);
        $this->assertSame($expect[0], $actual[0]->getRow()->getArrayCopy());
        $this->assertSame($expect[1], $actual[1]->getRow()->getArrayCopy());
        $this->assertSame($expect[2], $actual[2]->getRow()->getArrayCopy());

        $again = $this->mapper->fetchRecordSetBy(['id' => [1, 2, 3]]);
        $this->assertInstanceOf(RecordSet::CLASS, $again);
        $this->assertCount(3, $again);
        $this->assertInstanceOf(Record::CLASS, $again[0]);
        $this->assertInstanceOf(Record::CLASS, $again[1]);
        $this->assertInstanceOf(Record::CLASS, $again[2]);
        $this->assertSame($actual[0]->getRow(), $again[0]->getRow());
        $this->assertSame($actual[1]->getRow(), $again[1]->getRow());
        $this->assertSame($actual[2]->getRow(), $again[2]->getRow());

        $actual = $this->mapper->fetchRecordSetBy(['id' => [997, 998, 999]]);
        $this->assertSame(array(), $actual);
    }

    public function testSelectFetchRecordSet()
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

        $select = $this->mapper->select(['id' => [1, 2, 3]]);
        $actual = $select->fetchRecordSet();
        $this->assertCount(3, $actual);
        $this->assertSame($expect[0], $actual[0]->getRow()->getArrayCopy());
        $this->assertSame($expect[1], $actual[1]->getRow()->getArrayCopy());
        $this->assertSame($expect[2], $actual[2]->getRow()->getArrayCopy());

        $again = $select->fetchRecordSet();
        $this->assertCount(3, $again);
        $this->assertSame($actual[0]->getRow(), $again[0]->getRow());
        $this->assertSame($actual[1]->getRow(), $again[1]->getRow());
        $this->assertSame($actual[2]->getRow(), $again[2]->getRow());

        $select = $this->mapper->select(['id' => [997,998,999]]);
        $actual = $select->fetchRecordSet();
        $this->assertSame(array(), $actual);
    }

    public function testInsert()
    {
        $record = $this->mapper->newRecord([
            'id' => null,
            'name' => 'Mona',
            'building' => '10',
            'floor' => '99',
        ]);

        // does the insert *look* successful?
        $success = $this->mapper->insert($record);
        $this->assertTrue($success);

        // did the autoincrement ID get retained?
        $this->assertEquals(13, $record->id);

        // did it save in the identity map?
        $again = $this->mapper->fetchRecord(13);
        $this->assertSame($record->getRow(), $again->getRow());

        // was it *actually* inserted?
        $expect = [
            'id' => '13',
            'name' => 'Mona',
            'building' => '10',
            'floor' => '99',
        ];
        $actual = $this->mapper->getTable()->getReadConnection()->fetchOne(
            'SELECT * FROM employee WHERE id = 13'
        );
        $this->assertSame($expect, $actual);

        // try to insert again, should fail on unique name
        $this->silenceErrors();
        $this->setExpectedException(
            UnexpectedValueException::CLASS,
            "Expected 1 row affected, actual 0"
        );
        $this->mapper->insert($record);

        // try to insert a record of the wrong type
        $row = new FakeRow(new FakeRowIdentity(['id' => null]), []);
        $related = new Related([]);
        $record = new FakeRecord($row, $related);
        $this->setExpectedException(
            InvalidArgumentException::CLASS,
            "Expected object of type 'Atlas\Orm\DataSource\Employee\EmployeeRecord', got 'Atlas\Orm\Mapper\FakeRecord' instead."
        );
        $this->mapper->insert($record);
    }

    public function testUpdate()
    {
        // fetch a record, then modify and update it
        $record = $this->mapper->fetchRecordBy(['name' => 'Anna']);
        $record->name = 'Annabelle';

        // did the update *look* successful?
        $success = $this->mapper->update($record);
        $this->assertTrue($success);

        // is it still in the identity map?
        $again = $this->mapper->fetchRecordBy(['name' => 'Annabelle']);
        $this->assertSame($record->getRow(), $again->getRow());

        // was it *actually* updated?
        $expect = $record->getRow()->getArrayCopy();
        $actual = $this->mapper->getTable()->getReadConnection()->fetchOne(
            "SELECT * FROM employee WHERE name = 'Annabelle'"
        );
        $this->assertSame($expect, $actual);

        // try to update again, should be a no-op because there are no changes
        $this->assertNull($this->mapper->update($record));

        // delete the record and try to update it, should fail
        $this->assertTrue($this->mapper->delete($record));
        $record->name = 'Foo';
        $this->setExpectedException(
            UnexpectedValueException::CLASS,
            "Expected 1 row affected, actual 0"
        );
        $this->mapper->update($record);

        // try to update a record of the wrong type
        $row = new FakeRow(new FakeRowIdentity(['id' => null]), []);
        $related = new Related([]);
        $record = new FakeRecord($row, $related);
        $this->setExpectedException(
            InvalidArgumentException::CLASS,
            "Expected object of type 'Atlas\Orm\DataSource\Employee\EmployeeRecord', got 'Atlas\Orm\Mapper\FakeRecord' instead."
        );
        $this->mapper->update($record);
    }

    public function testDelete()
    {
        // fetch a record, then delete it
        $record = $this->mapper->fetchRecordBy(['name' => 'Anna']);
        $this->mapper->delete($record);

        // did it delete?
        $actual = $this->mapper->fetchRecordBy(['name' => 'Anna']);
        $this->assertFalse($actual);

        // do we still have everything else?
        $select = $this->mapper->select()->cols(['id'])->where('id > 0');
        $actual = $select->getTableSelect()->fetchAll();
        $expect = 11;
        $this->assertEquals($expect, count($actual));

        // try to update a record of the wrong type
        $row = new FakeRow(new FakeRowIdentity(['id' => null]), []);
        $related = new Related([]);
        $record = new FakeRecord($row, $related);
        $this->setExpectedException(
            InvalidArgumentException::CLASS,
            "Expected object of type 'Atlas\Orm\DataSource\Employee\EmployeeRecord', got 'Atlas\Orm\Mapper\FakeRecord' instead."
        );
        $this->mapper->delete($record);
    }

    protected function silenceErrors()
    {
        $conn = $this->mapper->getTable()->getWriteConnection();
        $conn->setAttribute($conn::ATTR_ERRMODE, $conn::ERRMODE_SILENT);
    }
}
