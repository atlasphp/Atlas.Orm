<?php
namespace Atlas\Orm\Table;

use Atlas\Orm\DataSource\Employee\EmployeeTable;
use Atlas\Orm\Exception;
use Atlas\Orm\SqliteFixture;
use Aura\Sql\ConnectionLocator;
use Aura\Sql\ExtendedPdo;
use Aura\SqlQuery\QueryFactory;

class TableTest extends \PHPUnit\Framework\TestCase
{
    protected $table;

    protected function setUp()
    {
        $this->table = new EmployeeTable(
            new ConnectionManager(new ConnectionLocator(function () {
                return new ExtendedPdo('sqlite::memory:');
            })),
            new QueryFactory('sqlite'),
            new IdentityMap(),
            new TableEvents()
        );

        $fixture = new SqliteFixture($this->table->getWriteConnection());
        $fixture->exec();
    }

    public function testTnsertUpdateDeleteRow()
    {
        $row = $this->table->newRow([
            'name' => 'Foobar',
            'building' => 99,
            'floor' => 1
        ]);
        $this->assertSame($row::FOR_INSERT, $row->getStatus());

        $this->table->insertRow($row);
        $this->assertSame($row::INSERTED, $row->getStatus());

        $row->name = 'Foobarx';
        $this->assertSame($row::MODIFIED, $row->getStatus());
        $this->table->updateRow($row);
        $this->assertSame($row::UPDATED, $row->getStatus());

        $this->table->deleteRow($row);
        $this->assertSame($row::DELETED, $row->getStatus());
    }

    public function testUpdateOnChangedPrimaryKey()
    {
        $row = $this->table->fetchRow(1);
        $row->id = 2;
        $this->expectException(
            Exception::CLASS,
            "Primary key value for 'id' changed from '1' to '2'"
        );
        $this->table->updateRow($row);
    }
}
