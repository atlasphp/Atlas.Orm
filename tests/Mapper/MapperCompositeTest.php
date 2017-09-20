<?php
namespace Atlas\Orm\Mapper;

use Atlas\Orm\DataSource\Course\CourseMapper;
use Atlas\Orm\DataSource\Course\CourseTable;
use Atlas\Orm\Relationship\Relationships;
use Atlas\Orm\SqliteFixture;
use Atlas\Orm\Table\ConnectionManager;
use Atlas\Orm\Table\TableEvents;
use Atlas\Orm\Table\IdentityMap;
use Aura\Sql\ConnectionLocator;
use Aura\Sql\ExtendedPdo;
use Aura\SqlQuery\QueryFactory;

class MapperCompositeTest extends \PHPUnit\Framework\TestCase
{
    protected $table;
    protected $mapper;

    protected function setUp()
    {
        parent::setUp();

        $this->mapper = new CourseMapper(
            new CourseTable(
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

        $fixture = new SqliteFixture($this->mapper->getWriteConnection());
        $fixture->exec();
    }

    public function testGetTable()
    {
        $this->assertInstanceOf(CourseTable::CLASS, $this->mapper->getTable());
    }

    public function testFetchRecord()
    {
        $expect = [
            'course_subject' => 'MATH',
            'course_number' => '100',
            'title' => 'Algebra',
        ];

        // fetch success
        $record1 = $this->mapper->fetchRecord([
            'course_subject' => 'MATH',
            'course_number' => '100',
        ]);
        $this->assertInstanceOf(Record::CLASS, $record1);
        $row1 = $record1->getRow();
        $this->assertSame($expect, $row1->getArrayCopy());

        // fetch again
        $record2 = $this->mapper->fetchRecord([
            'course_subject' => 'MATH',
            'course_number' => '100',
        ]);
        $this->assertInstanceOf(Record::CLASS, $record2);
        $this->assertNotSame($record1, $record2);
        $row2 = $record2->getRow();
        $this->assertSame($row1, $row2);

        // fetch failure
        $actual = $this->mapper->fetchRecord([
            'course_subject' => 'NONE',
            'course_number' => '999',
        ]);
        $this->assertNull($actual);
    }

    public function testFetchRecordBy()
    {
        $expect = [
            'course_subject' => 'MATH',
            'course_number' => '100',
            'title' => 'Algebra',
        ];

        // fetch success
        $record1 = $this->mapper->fetchRecordBy(['title' => 'Algebra']);
        $this->assertInstanceOf(Record::CLASS, $record1);
        $row1 = $record1->getRow();
        $this->assertSame($expect, $row1->getArrayCopy());

        // fetch again
        $record2 = $this->mapper->fetchRecordBy(['title' => 'Algebra']);
        $this->assertInstanceOf(Record::CLASS, $record2);
        $this->assertNotSame($record1, $record2);
        $row2 = $record2->getRow();
        $this->assertSame($row1, $row2);

        // fetch failure
        $actual = $this->mapper->fetchRecordBy(['title' => 'No Such Course']);
        $this->assertNull($actual);
    }

    public function testSelectFetchRecord()
    {
        $expect = [
            'course_subject' => 'MATH',
            'course_number' => '100',
            'title' => 'Algebra',
        ];

        // fetch success
        $select = $this->mapper->select([
            'course_subject' => 'MATH',
            'course_number' => '100',
        ]);
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
        $select = $this->mapper->select([
            'course_subject' => 'NONE',
            'course_number' => '999',
        ]);
        $actual = $select->fetchRecord();
        $this->assertNull($actual);
    }

    public function testFetchRecordSet()
    {
        $expect = [
            ['course_subject' => 'ENGL', 'course_number' => '100', 'title' => 'Composition'],
            ['course_subject' => 'HIST', 'course_number' => '200', 'title' => 'US History'],
            ['course_subject' => 'MATH', 'course_number' => '300', 'title' => 'Calculus'],
        ];

        $actual = $this->mapper->fetchRecordSet([
            ['course_subject' => 'ENGL', 'course_number' => '100'],
            ['course_subject' => 'HIST', 'course_number' => '200'],
            ['course_subject' => 'MATH', 'course_number' => '300'],
        ]);
        $this->assertInstanceOf(RecordSet::CLASS, $actual);
        $this->assertCount(3, $actual);
        $this->assertInstanceOf(Record::CLASS, $actual[0]);
        $this->assertInstanceOf(Record::CLASS, $actual[1]);
        $this->assertInstanceOf(Record::CLASS, $actual[2]);
        $this->assertSame($expect[0], $actual[0]->getRow()->getArrayCopy());
        $this->assertSame($expect[1], $actual[1]->getRow()->getArrayCopy());
        $this->assertSame($expect[2], $actual[2]->getRow()->getArrayCopy());

        $again = $this->mapper->fetchRecordSet([
            ['course_subject' => 'ENGL', 'course_number' => '100'],
            ['course_subject' => 'HIST', 'course_number' => '200'],
            ['course_subject' => 'MATH', 'course_number' => '300'],
        ]);
        $this->assertInstanceOf(RecordSet::CLASS, $again);
        $this->assertCount(3, $again);
        $this->assertInstanceOf(Record::CLASS, $again[0]);
        $this->assertInstanceOf(Record::CLASS, $again[1]);
        $this->assertInstanceOf(Record::CLASS, $again[2]);
        $this->assertSame($actual[0]->getRow(), $again[0]->getRow());
        $this->assertSame($actual[1]->getRow(), $again[1]->getRow());
        $this->assertSame($actual[2]->getRow(), $again[2]->getRow());

        $actual = $this->mapper->fetchRecordSet([
            ['course_subject' => 'ENGL', 'course_number' => '999'],
            ['course_subject' => 'HIST', 'course_number' => '999'],
            ['course_subject' => 'MATH', 'course_number' => '999'],
        ]);
        $this->assertTrue($actual->isEmpty());
    }

    public function testFetchRecordSetBy()
    {
        $expect = [
            ['course_subject' => 'HIST', 'course_number' => '100', 'title' => 'World History'],
            ['course_subject' => 'HIST', 'course_number' => '200', 'title' => 'US History'],
            ['course_subject' => 'HIST', 'course_number' => '300', 'title' => 'Victorian History'],
            ['course_subject' => 'HIST', 'course_number' => '400', 'title' => 'Recent History'],
        ];

        $actual = $this->mapper->fetchRecordSetBy(['course_subject' => 'HIST']);

        $this->assertInstanceOf(RecordSet::CLASS, $actual);
        $this->assertCount(4, $actual);
        $this->assertInstanceOf(Record::CLASS, $actual[0]);
        $this->assertInstanceOf(Record::CLASS, $actual[1]);
        $this->assertInstanceOf(Record::CLASS, $actual[2]);
        $this->assertSame($expect[0], $actual[0]->getRow()->getArrayCopy());
        $this->assertSame($expect[1], $actual[1]->getRow()->getArrayCopy());
        $this->assertSame($expect[2], $actual[2]->getRow()->getArrayCopy());
        $this->assertSame($expect[3], $actual[3]->getRow()->getArrayCopy());

        $again = $this->mapper->fetchRecordSetBy(['course_subject' => 'HIST']);
        $this->assertInstanceOf(RecordSet::CLASS, $again);
        $this->assertCount(4, $again);
        $this->assertInstanceOf(Record::CLASS, $again[0]);
        $this->assertInstanceOf(Record::CLASS, $again[1]);
        $this->assertInstanceOf(Record::CLASS, $again[2]);
        $this->assertSame($actual[0]->getRow(), $again[0]->getRow());
        $this->assertSame($actual[1]->getRow(), $again[1]->getRow());
        $this->assertSame($actual[2]->getRow(), $again[2]->getRow());
        $this->assertSame($actual[3]->getRow(), $again[3]->getRow());

        $actual = $this->mapper->fetchRecordSetBy(['course_subject' => 'NONE']);
        $this->assertTrue($actual->isEmpty());
    }

    public function testSelectFetchRecordSet()
    {
        $expect = [
            ['course_subject' => 'HIST', 'course_number' => '100', 'title' => 'World History'],
            ['course_subject' => 'HIST', 'course_number' => '200', 'title' => 'US History'],
            ['course_subject' => 'HIST', 'course_number' => '300', 'title' => 'Victorian History'],
            ['course_subject' => 'HIST', 'course_number' => '400', 'title' => 'Recent History'],
        ];

        $select = $this->mapper->select(['course_subject' => 'HIST']);
        $actual = $select->fetchRecordSet();
        $this->assertCount(4, $actual);
        $this->assertInstanceOf(Record::CLASS, $actual[0]);
        $this->assertInstanceOf(Record::CLASS, $actual[1]);
        $this->assertInstanceOf(Record::CLASS, $actual[2]);
        $this->assertSame($expect[0], $actual[0]->getRow()->getArrayCopy());
        $this->assertSame($expect[1], $actual[1]->getRow()->getArrayCopy());
        $this->assertSame($expect[2], $actual[2]->getRow()->getArrayCopy());
        $this->assertSame($expect[3], $actual[3]->getRow()->getArrayCopy());

        $again = $select->fetchRecordSet();
        $this->assertInstanceOf(RecordSet::CLASS, $again);
        $this->assertCount(4, $again);
        $this->assertInstanceOf(Record::CLASS, $again[0]);
        $this->assertInstanceOf(Record::CLASS, $again[1]);
        $this->assertInstanceOf(Record::CLASS, $again[2]);
        $this->assertSame($actual[0]->getRow(), $again[0]->getRow());
        $this->assertSame($actual[1]->getRow(), $again[1]->getRow());
        $this->assertSame($actual[2]->getRow(), $again[2]->getRow());
        $this->assertSame($actual[3]->getRow(), $again[3]->getRow());

        $select = $this->mapper->select(['course_subject' => 'NONE']);
        $actual = $select->fetchRecordSet();
        $this->assertTrue($actual->isEmpty());
    }

    public function testInsert()
    {
        $record = $this->mapper->newRecord([
            'course_subject' => 'PHIL',
            'course_number' => '100',
            'title' => 'Greek Philosophy',
        ]);

        // does the insert *look* successful?
        $success = $this->mapper->insert($record);
        $this->assertTrue($success);

        // did it save in the identity map?
        $again = $this->mapper->fetchRecord([
            'course_subject' => 'PHIL',
            'course_number' => '100',
        ]);
        $this->assertSame($record->getRow(), $again->getRow());

        // was it *actually* inserted?
        $expect = [
            'course_subject' => 'PHIL',
            'course_number' => '100',
            'title' => 'Greek Philosophy',
        ];
        $actual = $this->mapper->getReadConnection()->fetchOne(
            "SELECT * FROM courses WHERE course_subject = 'PHIL' AND course_number = '100'"
        );
        $this->assertSame($expect, $actual);

        // try to insert again, should fail on primary key repetition
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
        $record = $this->mapper->fetchRecordBy([
            'course_subject' => 'MATH',
            'course_number' => '100',
        ]);
        $record->title = 'Algebra I';

        // did the update *look* successful?
        $success = $this->mapper->update($record);
        $this->assertTrue($success);

        // is it still in the identity map?
        $again = $this->mapper->fetchRecordBy([
            'course_subject' => 'MATH',
            'course_number' => '100',
        ]);
        $this->assertSame($record->getRow(), $again->getRow());

        // was it *actually* updated?
        $expect = $record->getRow()->getArrayCopy();
        $actual = $this->mapper->getReadConnection()->fetchOne(
            "SELECT * FROM courses WHERE course_subject = 'MATH' AND course_number = '100'"
        );
        $this->assertSame($expect, $actual);

        // try to update again, should be a no-op because there are no changes
        $this->assertFalse($this->mapper->update($record));
    }

    public function testDelete()
    {
        // fetch a record, then delete it
        $record = $this->mapper->fetchRecordBy([
            'course_subject' => 'MATH',
            'course_number' => '100',
        ]);
        $this->mapper->delete($record);

        // did it delete?
        $actual = $this->mapper->fetchRecordBy([
            'course_subject' => 'MATH',
            'course_number' => '100',
        ]);
        $this->assertNull($actual);

        // do we still have everything else?
        $select = $this->mapper->select()->cols(['*']);
        $actual = $select->fetchAll();
        $expect = 11;
        $this->assertEquals($expect, count($actual));
    }

    protected function silenceErrors()
    {
        $conn = $this->mapper->getWriteConnection();
        $conn->setAttribute($conn::ATTR_ERRMODE, $conn::ERRMODE_SILENT);
    }
}
