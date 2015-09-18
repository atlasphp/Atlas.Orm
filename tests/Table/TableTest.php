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
    protected $gateway;

    protected function setUp()
    {
        parent::setUp();

        $connectionLocator = new ConnectionLocator(function () {
            return new ExtendedPdo('sqlite::memory:');
        });

        $this->gateway = new EmployeeTable(
            $connectionLocator,
            new QueryFactory('sqlite'),
            new IdentityMap(),
            new Filter()
        );

        $fixture = new SqliteFixture($this->gateway->getWriteConnection());
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
            new Filter()
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
            $this->gateway->getIdentityMap()
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
        $actual = $this->gateway->fetchRow(1);
        $this->assertSame($expect, $actual->getArrayCopy());

        $again = $this->gateway->fetchRow(1);
        $this->assertSame($again, $actual);

        $actual = $this->gateway->fetchRow(-1);
        $this->assertFalse($actual);
    }

    public function testFetchRowSet()
    {
        $this->assertSame([], $this->gateway->fetchRowSet([]));

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

        $actual = $this->gateway->fetchRowSet([1, 2, 3]);
        $this->assertCount(3, $actual);
        $this->assertSame($expect[0], $actual[0]->getArrayCopy());
        $this->assertSame($expect[1], $actual[1]->getArrayCopy());
        $this->assertSame($expect[2], $actual[2]->getArrayCopy());

        $again = $this->gateway->fetchRowSet([1, 2, 3]);
        $this->assertCount(3, $again);
        $this->assertSame($actual[0], $again[0]);
        $this->assertSame($actual[1], $again[1]);
        $this->assertSame($actual[2], $again[2]);

        $actual = $this->gateway->fetchRowSet([997, 998, 999]);
        $this->assertSame(array(), $actual);
    }

    public function testInsert()
    {
        $row = $this->gateway->newRow([
            'id' => null,
            'name' => 'Mona',
            'building' => '10',
            'floor' => '99',
        ]);

        // does the insert *look* successful?
        $success = $this->gateway->insert($row);
        $this->assertTrue($success);

        // did the autoincrement ID get retained?
        $this->assertEquals(13, $row->id);

        // did it save in the identity map?
        $again = $this->gateway->fetchRow(13);
        $this->assertSame($row, $again);

        // was it *actually* inserted?
        $expect = [
            'id' => '13',
            'name' => 'Mona',
            'building' => '10',
            'floor' => '99',
        ];
        $actual = $this->gateway->getReadConnection()->fetchOne(
            'SELECT * FROM employees WHERE id = 13'
        );
        $this->assertSame($expect, $actual);

        // try to insert again, should fail on unique name
        $this->silenceErrors();
        $this->assertFalse($this->gateway->insert($row));
    }

    public function testUpdate()
    {
        // fetch an object, then modify and update it
        $row = $this->gateway->fetchRowBy(['name' => 'Anna']);
        $row->name = 'Annabelle';

        // did the update *look* successful?
        $success = $this->gateway->update($row);
        $this->assertTrue($success);

        // is it still in the identity map?
        $again = $this->gateway->fetchRowBy(['name' => 'Annabelle']);
        $this->assertSame($row, $again);

        // was it *actually* updated?
        $expect = $row->getArrayCopy();
        $actual = $this->gateway->getReadConnection()->fetchOne(
            "SELECT * FROM employees WHERE name = 'Annabelle'"
        );
        $this->assertSame($expect, $actual);
    }

    public function testDelete()
    {
        // fetch an object, then delete it
        $row = $this->gateway->fetchRowBy(['name' => 'Anna']);
        $this->gateway->delete($row);

        // did it delete?
        $actual = $this->gateway->fetchRowBy(['name' => 'Anna']);
        $this->assertFalse($actual);

        // do we still have everything else?
        $actual = $this->gateway->fetchRowSetBySelect(
            $this->gateway->select()->where('id > 0')
        );
        $expect = 11;
        $this->assertEquals($expect, count($actual));
    }

    protected function silenceErrors()
    {
        $conn = $this->gateway->getWriteConnection();
        $conn->setAttribute($conn::ATTR_ERRMODE, $conn::ERRMODE_SILENT);
    }
}
