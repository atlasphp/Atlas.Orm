<?php
namespace Atlas\Orm\Table;

use Atlas\Orm\DataSource\Employee\EmployeeTable;
use Atlas\Orm\Exception;
use Atlas\Orm\SqliteFixture;
use Aura\Sql\ConnectionLocator;
use Aura\Sql\ExtendedPdo;
use Aura\SqlQuery\QueryFactory;
use Iterator;

class TableSelectTest extends \PHPUnit\Framework\TestCase
{
    protected $select;

    protected function setUp()
    {
        $table = new EmployeeTable(
            new ConnectionManager(new ConnectionLocator(function () {
                return new ExtendedPdo('sqlite::memory:');
            })),
            new QueryFactory('sqlite'),
            new IdentityMap(),
            new TableEvents()
        );

        $fixture = new SqliteFixture($table->getWriteConnection());
        $fixture->exec();

        $this->select = $table->select();
    }

    public function testYieldAssoc()
    {
        $actual = $this->select->cols(['id', 'name'])->where('id > 0')->yieldAssoc();
        $this->assertInstanceOf(Iterator::CLASS, $actual);
    }

    public function testYieldPairs()
    {
        $actual = $this->select->cols(['id', 'name'])->where('id > 0')->yieldPairs();
        $this->assertInstanceOf(Iterator::CLASS, $actual);
    }

    public function testYieldCol()
    {
        $actual = $this->select->cols(['name'])->where('id > 0')->yieldCol();
        $this->assertInstanceOf(Iterator::CLASS, $actual);
    }
}
