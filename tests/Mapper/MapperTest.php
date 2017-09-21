<?php
namespace Atlas\Orm\Mapper;

use Atlas\Orm\Assertions;
use Atlas\Orm\DataSource\Employee\BadEmployeeMapper;
use Atlas\Orm\DataSource\Employee\EmployeeMapper;
use Atlas\Orm\DataSource\Employee\EmployeeTable;
use Atlas\Orm\Exception;
use Atlas\Orm\Relationship\Relationships;
use Atlas\Orm\SqliteFixture;
use Atlas\Orm\Table\ConnectionManager;
use Atlas\Orm\Table\IdentityMap;
use Atlas\Orm\Table\Row;
use Atlas\Orm\Table\TableEvents;
use Aura\Sql\ConnectionLocator;
use Aura\Sql\ExtendedPdo;
use Aura\SqlQuery\QueryFactory;

class MapperTest extends \PHPUnit\Framework\TestCase
{
    use Assertions;

    protected $table;
    protected $mapper;
    protected $connection;
    protected $connectionLocator;

    protected function setUp()
    {
        parent::setUp();

        $pdo = new ExtendedPdo('sqlite::memory:');
        $this->connection = $pdo;

        $this->connectionLocator = new ConnectionLocator(function () use ($pdo) {
            return $pdo;
        });

        $this->mapper = new EmployeeMapper(
            new EmployeeTable(
                new ConnectionManager($this->connectionLocator),
                new QueryFactory('sqlite'),
                new IdentityMap(),
                new TableEvents()
            ),
            new Relationships(new MapperLocator()),
            new MapperEvents()
        );

        $fixture = new SqliteFixture($this->mapper->getWriteConnection());
        $fixture->exec();
    }

    public function testGetConnections()
    {
        $actual = $this->mapper->getReadConnection();
        $this->assertSame($actual, $this->connection);

        $actual = $this->mapper->getWriteConnection();
        $this->assertSame($actual, $this->connection);
    }

    public function testGetTable()
    {
        $this->assertInstanceOf(EmployeeTable::CLASS, $this->mapper->getTable());
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
        $this->assertNull($actual);
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
        $this->assertNull($actual);
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
        $this->assertNull($actual);
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
        $this->assertTrue($actual->isEmpty());
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
        $this->assertTrue($actual->isEmpty());
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
        $this->assertTrue($actual->isEmpty());
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
        $actual = $this->mapper->getReadConnection()->fetchOne(
            'SELECT * FROM employee WHERE id = 13'
        );
        $this->assertSame($expect, $actual);

        // try to insert again, should fail on unique name
        $this->silenceErrors();
        $this->expectException(
            'Atlas\Orm\Exception',
            "Expected 1 row affected, actual 0"
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
        $actual = $this->mapper->getReadConnection()->fetchOne(
            "SELECT * FROM employee WHERE name = 'Annabelle'"
        );
        $this->assertSame($expect, $actual);

        // try to update again, should be a no-op because there are no changes
        $this->assertFalse($this->mapper->update($record));

        // delete the record and try to update it, should fail.
        // fake the row status to force the test.
        $this->assertTrue($this->mapper->delete($record));
        $record->getRow()->setStatus(Row::SELECTED);
        $record->name = 'Foo';
        $this->expectException(
            'Atlas\Orm\Exception',
            "Expected 1 row affected, actual 0"
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
        $this->assertNull($actual);

        // do we still have everything else?
        $select = $this->mapper->select()->cols(['id'])->where('id > 0');
        $actual = $select->fetchAll();
        $expect = 11;
        $this->assertEquals($expect, count($actual));

        // try to delete the record again
        $this->expectException(
            'Atlas\Orm\Exception',
            "Expected 1 row affected, actual 0"
        );
        $this->mapper->delete($record);
    }

    public function testSelect_numericCol()
    {
        $this->expectException(
            'Atlas\Orm\Exception',
            "Expected non-numeric column name, got '0' instead."
        );
        $this->mapper->select(['foo']);
    }

    public function testSelect_null()
    {
        $select = $this->mapper->select(['foo' => null])->cols(['foo']);
        $expect = 'SELECT
                foo
            FROM
                "employee"
            WHERE
                "employee"."foo" IS NULL
        ';
        $actual = $select->__toString();
        $this->assertSameSql($expect, $actual);
    }

    public function testCloneSelectDontAlterOriginalQuery()
    {
        $select = $this->mapper->select(['foo' => null])->cols(['foo']);
        $expect = 'SELECT
                foo
            FROM
                "employee"
            WHERE
                "employee"."foo" IS NULL
        ';
        $counter = clone $select;
        $counter->resetCols()
            ->cols(['COUNT(*)']);
        $actual = $select->__toString();
        $this->assertSameSql($expect, $actual);

        $expect = 'SELECT
                COUNT(*)
            FROM
                "employee"
            WHERE
                "employee"."foo" IS NULL
        ';
        $actual = $counter->__toString();
        $this->assertSameSql($expect, $actual);
    }

    protected function silenceErrors()
    {
        $conn = $this->mapper->getWriteConnection();
        $conn->setAttribute($conn::ATTR_ERRMODE, $conn::ERRMODE_SILENT);
    }

    public function testCannotUseRowNameForRelated()
    {
        $this->expectException(
            Exception::CLASS,
            "Relationship 'name' conflicts with existing column name."
        );

        $badMmapper = new BadEmployeeMapper(
            new EmployeeTable(
                new ConnectionManager(
                    new ConnectionLocator(function () {
                        return new ExtendedPdo('sqlite::memory:');
                    })
                ),
                new QueryFactory('sqlite'),
                new IdentityMap(),
                new TableEvents()
            ),
            new Relationships(new MapperLocator()),
            new MapperEvents()
        );
    }
}
