<?php
namespace Atlas\Table;

use Atlas\Fake\Auto\AutoTable;
use Atlas\Fake\Employee\EmployeeTable;
use Atlas\SqliteFixture;
use Aura\Sql\ConnectionLocator;
use Aura\Sql\ExtendedPdo;
use Aura\SqlQuery\QueryFactory;

class TableTest extends \PHPUnit_Framework_TestCase
{
    protected $table;

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
    }

    public function testAuto()
    {
        $connectionLocator = new ConnectionLocator(function () {
            return new ExtendedPdo('sqlite::memory:');
        });

        $auto = new AutoTable(
            $connectionLocator,
            new QueryFactory('sqlite'),
            new IdentityMap(),
            new RowFilter()
        );

        $this->assertSame('auto', $auto->getTable());
        $this->assertSame('auto_id', $auto->getPrimary());
        $this->assertTrue($auto->getAutoinc());
        $this->assertSame('Atlas\Table\Row', $auto->getRowClass());
        $this->assertSame('Atlas\Table\RowSet', $auto->getRowSetClass());
    }

    public function testGetIdentityMap()
    {
        $this->assertInstanceOf(
            'Atlas\Table\IdentityMap',
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
        $this->assertSame($expect, $actual->getArrayCopy());

        $again = $this->table->fetchRow(1);
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
        $this->assertSame($expect, $actual->getArrayCopy());

        $again = $this->table->fetchRowBy(['id' => 1]);
        $this->assertSame($again, $actual);

        $actual = $this->table->fetchRowBy(['id' => -1]);
        $this->assertFalse($actual);
    }

    public function testFetchRows()
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

        $actual = $this->table->fetchRows([1, 2, 3]);
        $this->assertCount(3, $actual);
        $this->assertSame($expect['1'], $actual['1']->getArrayCopy());
        $this->assertSame($expect['2'], $actual['2']->getArrayCopy());
        $this->assertSame($expect['3'], $actual['3']->getArrayCopy());

        $again = $this->table->fetchRows([1, 2, 3]);
        $this->assertCount(3, $again);
        $this->assertSame($actual['1'], $again['1']);
        $this->assertSame($actual['2'], $again['2']);
        $this->assertSame($actual['3'], $again['3']);

        $actual = $this->table->fetchRows([997, 998, 999]);
        $this->assertSame(array(), $actual);
    }

    public function testFetchRowsBy()
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

        $actual = $this->table->fetchRowsBy(['building' => '1'], 'id');
        $this->assertCount(6, $actual);
        $this->assertSame($expect['1'], $actual['1']->getArrayCopy());
        $this->assertSame($expect['2'], $actual['2']->getArrayCopy());
        $this->assertSame($expect['3'], $actual['3']->getArrayCopy());
        $this->assertSame($expect['4'], $actual['4']->getArrayCopy());
        $this->assertSame($expect['5'], $actual['5']->getArrayCopy());
        $this->assertSame($expect['6'], $actual['6']->getArrayCopy());

        $again = $this->table->fetchRowsBy(['building' => '1'], 'id');
        $this->assertCount(6, $again);
        $this->assertSame($actual['1'], $again['1']);
        $this->assertSame($actual['2'], $again['2']);
        $this->assertSame($actual['3'], $again['3']);
        $this->assertSame($actual['4'], $again['4']);
        $this->assertSame($actual['5'], $again['5']);
        $this->assertSame($actual['6'], $again['6']);

        $actual = $this->table->fetchRowsBy(['building' => '99'], 'id');
        $this->assertSame(array(), $actual);
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
        $this->assertCount(3, $actual);
        $this->assertSame($expect[0], $actual[0]->getArrayCopy());
        $this->assertSame($expect[1], $actual[1]->getArrayCopy());
        $this->assertSame($expect[2], $actual[2]->getArrayCopy());

        $again = $this->table->fetchRowSet([1, 2, 3]);
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

    public function testFetchRowSets()
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

        $actualRowSets = $this->table->fetchRowSets([1, 2, 3, 4, 5, 6], 'floor');
        $this->assertCount(3, $actualRowSets);
        foreach ($actualRowSets as $floor => $actualRowSet) {
            $this->assertCount(2, $actualRowSet);
            $this->assertSame($expect[$floor][0], $actualRowSet[0]->getArrayCopy());
            $this->assertSame($expect[$floor][1], $actualRowSet[1]->getArrayCopy());
        }
    }

    public function testFetchRowSetsBy()
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

        $actualRowSets = $this->table->fetchRowSetsBy(['id' => [1, 2, 3, 4, 5, 6]], 'floor');
        $this->assertCount(3, $actualRowSets);
        foreach ($actualRowSets as $floor => $actualRowSet) {
            $this->assertCount(2, $actualRowSet);
            $this->assertSame($expect[$floor][0], $actualRowSet[0]->getArrayCopy());
            $this->assertSame($expect[$floor][1], $actualRowSet[1]->getArrayCopy());
        }
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
            'SELECT * FROM employees WHERE id = 13'
        );
        $this->assertSame($expect, $actual);

        // try to insert again, should fail on unique name
        $this->silenceErrors();
        $this->assertFalse($this->table->insert($row));
    }

    public function testUpdate()
    {
        // fetch an object, then modify and update it
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
            "SELECT * FROM employees WHERE name = 'Annabelle'"
        );
        $this->assertSame($expect, $actual);

        // try to update again, should be a no-op because there are no changes
        $this->assertNull($this->table->update($row));

        // delete the row and try to update it, should fail
        $this->assertTrue($this->table->delete($row));
        $row->name = 'Foo';
        $this->assertFalse($this->table->update($row));
    }

    public function testDelete()
    {
        // fetch an object, then delete it
        $row = $this->table->fetchRowBy(['name' => 'Anna']);
        $this->table->delete($row);

        // did it delete?
        $actual = $this->table->fetchRowBy(['name' => 'Anna']);
        $this->assertFalse($actual);

        // do we still have everything else?
        $actual = $this->table->fetchRowSetBySelect(
            $this->table->select()->where('id > 0')
        );
        $expect = 11;
        $this->assertEquals($expect, count($actual));
    }

    protected function silenceErrors()
    {
        $conn = $this->table->getWriteConnection();
        $conn->setAttribute($conn::ATTR_ERRMODE, $conn::ERRMODE_SILENT);
    }

    public function testSelectCustom()
    {
        $select = $this->table->select(['name' => 'Anna'], function ($select) {
            $select->cols(['id']);
            $select->limit(10);
        });

        $expect = 'SELECT
    id
FROM
    "employees"
WHERE
    "employees"."name" = :_1_
LIMIT 10';

        $actual = $select->__toString();
        $this->assertSame($expect, $actual);
    }

    public function testSelectWhereNull()
    {
        $select = $this->table->select(['name' => null], function ($select) {
            $select->cols(['id']);
        });

        $expect = 'SELECT
    id
FROM
    "employees"
WHERE
    "employees"."name" IS NULL';

        $actual = $select->__toString();
        $this->assertSame($expect, $actual);
    }
}
