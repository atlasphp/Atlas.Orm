<?php
namespace Atlas\Mapper;

use Atlas\Fake\Auto\AutoMapper;
use Atlas\Fake\Auto\AutoTable;
use Atlas\Fake\Employee\EmployeeMapper;
use Atlas\Fake\Employee\EmployeeTable;
use Atlas\Mapper\RecordFactory;
use Atlas\Mapper\Relations;
use Atlas\SqliteFixture;
use Atlas\Table\IdentityMap;
use Atlas\Table\RowFilter;
use Aura\Sql\ConnectionLocator;
use Aura\Sql\ExtendedPdo;
use Aura\SqlQuery\QueryFactory;

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
            new RowFilter()
        );

        $fixture = new SqliteFixture($this->table->getWriteConnection());
        $fixture->exec();

        $this->mapper = new EmployeeMapper($this->table, new RecordFactory());
    }

    public function testGetTable()
    {
        $this->assertSame($this->table, $this->mapper->getTable());
    }

    public function testGetRelations()
    {
        $this->assertInstanceOf(Relations::CLASS, $this->mapper->getRelations());
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

    public function testFetchRecordBySelect()
    {
        $expect = [
            'id' => '1',
            'name' => 'Anna',
            'building' => '1',
            'floor' => '1',
        ];

        // fetch success
        $select = $this->mapper->select(['id' => '1']);
        $record1 = $this->mapper->fetchRecordBySelect($select);
        $this->assertInstanceOf(Record::CLASS, $record1);
        $row1 = $record1->getRow();
        $this->assertSame($expect, $row1->getArrayCopy());

        // fetch again
        $record2 = $this->mapper->fetchRecordBySelect($select);
        $this->assertInstanceOf(Record::CLASS, $record2);
        $this->assertNotSame($record1, $record2);
        $row2 = $record2->getRow();
        $this->assertSame($row1, $row2);

        // fetch failure
        $select = $this->mapper->select(['id' => '-1']);
        $actual = $this->mapper->fetchRecordBySelect($select);
        $this->assertFalse($actual);
    }

    public function testFetchRecords()
    {
        $expect = [
            '1' => [
                'id' => '1',
                'name' => 'Anna',
                'building' => '1',
                'floor' => '1',
            ],
            '2' => [
                'id' => '2',
                'name' => 'Betty',
                'building' => '1',
                'floor' => '2',
            ],
            '3' => [
                'id' => '3',
                'name' => 'Clara',
                'building' => '1',
                'floor' => '3',
            ],
        ];

        $actual = $this->mapper->fetchRecords([1, 2, 3]);
        $this->assertTrue(is_array($actual));
        $this->assertCount(3, $actual);
        $this->assertInstanceOf(Record::CLASS, $actual['1']);
        $this->assertInstanceOf(Record::CLASS, $actual['2']);
        $this->assertInstanceOf(Record::CLASS, $actual['3']);
        $this->assertSame($expect['1'], $actual['1']->getRow()->getArrayCopy());
        $this->assertSame($expect['2'], $actual['2']->getRow()->getArrayCopy());
        $this->assertSame($expect['3'], $actual['3']->getRow()->getArrayCopy());

        $again = $this->mapper->fetchRecords([1, 2, 3]);
        $this->assertTrue(is_array($again));
        $this->assertCount(3, $again);
        $this->assertInstanceOf(Record::CLASS, $again['1']);
        $this->assertInstanceOf(Record::CLASS, $again['2']);
        $this->assertInstanceOf(Record::CLASS, $again['3']);
        $this->assertSame($actual['1']->getRow(), $again['1']->getRow());
        $this->assertSame($actual['2']->getRow(), $again['2']->getRow());
        $this->assertSame($actual['3']->getRow(), $again['3']->getRow());

        $actual = $this->mapper->fetchRecords([997, 998, 999]);
        $this->assertSame(array(), $actual);
    }

    public function testFetchRecordsBy()
    {
        $expect = array (
            '1' => array (
                'id' => '1',
                'name' => 'Anna',
                'building' => '1',
                'floor' => '1',
            ),
            '2' => array (
                'id' => '2',
                'name' => 'Betty',
                'building' => '1',
                'floor' => '2',
            ),
            '3' => array (
                'id' => '3',
                'name' => 'Clara',
                'building' => '1',
                'floor' => '3',
            ),
            '4' => array (
                'id' => '4',
                'name' => 'Donna',
                'building' => '1',
                'floor' => '1',
            ),
            '5' => array (
                'id' => '5',
                'name' => 'Edna',
                'building' => '1',
                'floor' => '2',
            ),
            '6' => array (
                'id' => '6',
                'name' => 'Fiona',
                'building' => '1',
                'floor' => '3',
            ),
        );

        $actual = $this->mapper->fetchRecordsBy(['building' => '1'], 'id');
        $this->assertTrue(is_array($actual));
        $this->assertCount(6, $actual);
        $this->assertInstanceOf(Record::CLASS, $actual['1']);
        $this->assertInstanceOf(Record::CLASS, $actual['2']);
        $this->assertInstanceOf(Record::CLASS, $actual['3']);
        $this->assertInstanceOf(Record::CLASS, $actual['4']);
        $this->assertInstanceOf(Record::CLASS, $actual['5']);
        $this->assertInstanceOf(Record::CLASS, $actual['6']);
        $this->assertSame($expect['1'], $actual['1']->getRow()->getArrayCopy());
        $this->assertSame($expect['2'], $actual['2']->getRow()->getArrayCopy());
        $this->assertSame($expect['3'], $actual['3']->getRow()->getArrayCopy());
        $this->assertSame($expect['4'], $actual['4']->getRow()->getArrayCopy());
        $this->assertSame($expect['5'], $actual['5']->getRow()->getArrayCopy());
        $this->assertSame($expect['6'], $actual['6']->getRow()->getArrayCopy());

        $again = $this->mapper->fetchRecordsBy(['building' => '1'], 'id');
        $this->assertTrue(is_array($again));
        $this->assertCount(6, $again);
        $this->assertInstanceOf(Record::CLASS, $again['1']);
        $this->assertInstanceOf(Record::CLASS, $again['2']);
        $this->assertInstanceOf(Record::CLASS, $again['3']);
        $this->assertInstanceOf(Record::CLASS, $again['4']);
        $this->assertInstanceOf(Record::CLASS, $again['5']);
        $this->assertInstanceOf(Record::CLASS, $again['6']);
        $this->assertSame($actual['1']->getRow(), $again['1']->getRow());
        $this->assertSame($actual['2']->getRow(), $again['2']->getRow());
        $this->assertSame($actual['3']->getRow(), $again['3']->getRow());
        $this->assertSame($actual['4']->getRow(), $again['4']->getRow());
        $this->assertSame($actual['5']->getRow(), $again['5']->getRow());
        $this->assertSame($actual['6']->getRow(), $again['6']->getRow());

        $actual = $this->mapper->fetchRecordsBy(['building' => '99'], 'id');
        $this->assertSame(array(), $actual);
    }

    public function testFetchRecordsBySelect()
    {
        $expect = array (
            '1' => array (
                'id' => '1',
                'name' => 'Anna',
                'building' => '1',
                'floor' => '1',
            ),
            '2' => array (
                'id' => '2',
                'name' => 'Betty',
                'building' => '1',
                'floor' => '2',
            ),
            '3' => array (
                'id' => '3',
                'name' => 'Clara',
                'building' => '1',
                'floor' => '3',
            ),
            '4' => array (
                'id' => '4',
                'name' => 'Donna',
                'building' => '1',
                'floor' => '1',
            ),
            '5' => array (
                'id' => '5',
                'name' => 'Edna',
                'building' => '1',
                'floor' => '2',
            ),
            '6' => array (
                'id' => '6',
                'name' => 'Fiona',
                'building' => '1',
                'floor' => '3',
            ),
        );

        $select = $this->mapper->select(['building' => '1']);
        $actual = $this->mapper->fetchRecordsBySelect($select, 'id');
        $this->assertTrue(is_array($actual));
        $this->assertCount(6, $actual);
        $this->assertInstanceOf(Record::CLASS, $actual['1']);
        $this->assertInstanceOf(Record::CLASS, $actual['2']);
        $this->assertInstanceOf(Record::CLASS, $actual['3']);
        $this->assertInstanceOf(Record::CLASS, $actual['4']);
        $this->assertInstanceOf(Record::CLASS, $actual['5']);
        $this->assertInstanceOf(Record::CLASS, $actual['6']);
        $this->assertSame($expect['1'], $actual['1']->getRow()->getArrayCopy());
        $this->assertSame($expect['2'], $actual['2']->getRow()->getArrayCopy());
        $this->assertSame($expect['3'], $actual['3']->getRow()->getArrayCopy());
        $this->assertSame($expect['4'], $actual['4']->getRow()->getArrayCopy());
        $this->assertSame($expect['5'], $actual['5']->getRow()->getArrayCopy());
        $this->assertSame($expect['6'], $actual['6']->getRow()->getArrayCopy());

        $again = $this->mapper->fetchRecordsBySelect($select, 'id');
        $this->assertTrue(is_array($again));
        $this->assertCount(6, $again);
        $this->assertInstanceOf(Record::CLASS, $again['1']);
        $this->assertInstanceOf(Record::CLASS, $again['2']);
        $this->assertInstanceOf(Record::CLASS, $again['3']);
        $this->assertInstanceOf(Record::CLASS, $again['4']);
        $this->assertInstanceOf(Record::CLASS, $again['5']);
        $this->assertInstanceOf(Record::CLASS, $again['6']);
        $this->assertSame($actual['1']->getRow(), $again['1']->getRow());
        $this->assertSame($actual['2']->getRow(), $again['2']->getRow());
        $this->assertSame($actual['3']->getRow(), $again['3']->getRow());
        $this->assertSame($actual['4']->getRow(), $again['4']->getRow());
        $this->assertSame($actual['5']->getRow(), $again['5']->getRow());
        $this->assertSame($actual['6']->getRow(), $again['6']->getRow());

        $select = $this->mapper->select(['building' => '99']);
        $actual = $this->mapper->fetchRecordsBySelect($select, 'id');
        $this->assertSame(array(), $actual);
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

    public function testFetchRecordSetBySelect()
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
        $actual = $this->mapper->fetchRecordSetBySelect($select);
        $this->assertCount(3, $actual);
        $this->assertSame($expect[0], $actual[0]->getRow()->getArrayCopy());
        $this->assertSame($expect[1], $actual[1]->getRow()->getArrayCopy());
        $this->assertSame($expect[2], $actual[2]->getRow()->getArrayCopy());

        $again = $this->mapper->fetchRecordSetBySelect($select);
        $this->assertCount(3, $again);
        $this->assertSame($actual[0]->getRow(), $again[0]->getRow());
        $this->assertSame($actual[1]->getRow(), $again[1]->getRow());
        $this->assertSame($actual[2]->getRow(), $again[2]->getRow());

        $select = $this->mapper->select(['id' => [997,998,999]]);
        $actual = $this->mapper->fetchRecordSetBySelect($select);
        $this->assertSame(array(), $actual);
    }

    public function testFetchRecordSets()
    {
        $expect = [
            1 => [
                0 => [
                    'id' => '1',
                    'name' => 'Anna',
                    'building' => '1',
                    'floor' => '1',
                ],
                1 => [
                    'id' => '4',
                    'name' => 'Donna',
                    'building' => '1',
                    'floor' => '1',
                ],
            ],
            2 => [
                0 => [
                    'id' => '2',
                    'name' => 'Betty',
                    'building' => '1',
                    'floor' => '2',
                ],
                1 => [
                    'id' => '5',
                    'name' => 'Edna',
                    'building' => '1',
                    'floor' => '2',
                ],
            ],
            3 => [
                0 => [
                    'id' => '3',
                    'name' => 'Clara',
                    'building' => '1',
                    'floor' => '3',
                ],
                1 => [
                    'id' => '6',
                    'name' => 'Fiona',
                    'building' => '1',
                    'floor' => '3',
                ],
            ],
        ];

        $actualRecordSets = $this->mapper->fetchRecordSets([1, 2, 3, 4, 5, 6], 'floor');
        $this->assertTrue(is_array($actualRecordSets));
        $this->assertCount(3, $actualRecordSets);
        foreach ($actualRecordSets as $floor => $actualRecordSet) {
            $this->assertInstanceOf(RecordSet::CLASS, $actualRecordSet);
            $this->assertCount(2, $actualRecordSet);
            $this->assertSame($expect[$floor][0], $actualRecordSet[0]->getRow()->getArrayCopy());
            $this->assertSame($expect[$floor][1], $actualRecordSet[1]->getRow()->getArrayCopy());
        }
    }

    public function testFetchRecordSetsBy()
    {
        $expect = [
            1 => [
                0 => [
                    'id' => '1',
                    'name' => 'Anna',
                    'building' => '1',
                    'floor' => '1',
                ],
                1 => [
                    'id' => '4',
                    'name' => 'Donna',
                    'building' => '1',
                    'floor' => '1',
                ],
            ],
            2 => [
                0 => [
                    'id' => '2',
                    'name' => 'Betty',
                    'building' => '1',
                    'floor' => '2',
                ],
                1 => [
                    'id' => '5',
                    'name' => 'Edna',
                    'building' => '1',
                    'floor' => '2',
                ],
            ],
            3 => [
                0 => [
                    'id' => '3',
                    'name' => 'Clara',
                    'building' => '1',
                    'floor' => '3',
                ],
                1 => [
                    'id' => '6',
                    'name' => 'Fiona',
                    'building' => '1',
                    'floor' => '3',
                ],
            ],
        ];

        $actualRecordSets = $this->mapper->fetchRecordSetsBy(['id' => [1, 2, 3, 4, 5, 6]], 'floor');
        $this->assertTrue(is_array($actualRecordSets));
        $this->assertCount(3, $actualRecordSets);
        foreach ($actualRecordSets as $floor => $actualRecordSet) {
            $this->assertInstanceOf(RecordSet::CLASS, $actualRecordSet);
            $this->assertCount(2, $actualRecordSet);
            $this->assertSame($expect[$floor][0], $actualRecordSet[0]->getRow()->getArrayCopy());
            $this->assertSame($expect[$floor][1], $actualRecordSet[1]->getRow()->getArrayCopy());
        }
    }

    public function testFetchRecordSetsBySelect()
    {
        $expect = [
            1 => [
                0 => [
                    'id' => '1',
                    'name' => 'Anna',
                    'building' => '1',
                    'floor' => '1',
                ],
                1 => [
                    'id' => '4',
                    'name' => 'Donna',
                    'building' => '1',
                    'floor' => '1',
                ],
            ],
            2 => [
                0 => [
                    'id' => '2',
                    'name' => 'Betty',
                    'building' => '1',
                    'floor' => '2',
                ],
                1 => [
                    'id' => '5',
                    'name' => 'Edna',
                    'building' => '1',
                    'floor' => '2',
                ],
            ],
            3 => [
                0 => [
                    'id' => '3',
                    'name' => 'Clara',
                    'building' => '1',
                    'floor' => '3',
                ],
                1 => [
                    'id' => '6',
                    'name' => 'Fiona',
                    'building' => '1',
                    'floor' => '3',
                ],
            ],
        ];

        $select = $this->mapper->select(['id' => [1, 2, 3, 4, 5, 6]]);
        $actualRecordSets = $this->mapper->fetchRecordSetsBySelect($select, 'floor');
        $this->assertTrue(is_array($actualRecordSets));
        $this->assertCount(3, $actualRecordSets);
        foreach ($actualRecordSets as $floor => $actualRecordSet) {
            $this->assertInstanceOf(RecordSet::CLASS, $actualRecordSet);
            $this->assertCount(2, $actualRecordSet);
            $this->assertSame($expect[$floor][0], $actualRecordSet[0]->getRow()->getArrayCopy());
            $this->assertSame($expect[$floor][1], $actualRecordSet[1]->getRow()->getArrayCopy());
        }
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
            'SELECT * FROM employees WHERE id = 13'
        );
        $this->assertSame($expect, $actual);

        // try to insert again, should fail on unique name
        $this->silenceErrors();
        $this->assertFalse($this->mapper->insert($record));
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
            "SELECT * FROM employees WHERE name = 'Annabelle'"
        );
        $this->assertSame($expect, $actual);

        // try to update again, should be a no-op because there are no changes
        $this->assertNull($this->mapper->update($record));

        // delete the record and try to update it, should fail
        $this->assertTrue($this->mapper->delete($record));
        $record->name = 'Foo';
        $this->assertFalse($this->mapper->update($record));
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
        $actual = $this->mapper->fetchRecordSetBySelect(
            $this->mapper->select()->where('id > 0')
        );
        $expect = 11;
        $this->assertEquals($expect, count($actual));
    }

    protected function silenceErrors()
    {
        $conn = $this->mapper->getTable()->getWriteConnection();
        $conn->setAttribute($conn::ATTR_ERRMODE, $conn::ERRMODE_SILENT);
    }
}
