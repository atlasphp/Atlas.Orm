<?php
namespace Atlas\Table;

use Atlas\Assertions;
use Atlas\Fake\Employee\EmployeeTable;
use Atlas\Fake\Employee\EmployeeRowFilter;
use Atlas\SqliteFixture;
use Aura\Sql\ConnectionLocator;
use Aura\Sql\ExtendedPdo;
use Aura\SqlQuery\QueryFactory;

class TableSelectTest extends \PHPUnit_Framework_TestCase
{
    use Assertions;

    protected $decoratedSelect;
    protected $tableSelect;

    protected function setUp()
    {
        $connectionLocator = new ConnectionLocator(function () {
            return new ExtendedPdo('sqlite::memory:');
        });

        $table = new EmployeeTable(
            $connectionLocator,
            new QueryFactory('sqlite'),
            new IdentityMap(),
            new EmployeeRowFilter()
        );

        $fixture = new SqliteFixture($table->getWriteConnection());
        $fixture->exec();

        $this->tableSelect = $table->select();
    }

    public function testGetStatement()
    {
        $this->tableSelect->cols(['*']);
        $expect = '
            SELECT
                *
            FROM
                "employee"
        ';
        $actual = $this->tableSelect->getStatement();
        $this->assertSameSql($expect, $actual);
    }

    public function testGetBindValues()
    {
        $expect = ['foo' => 'bar'];
        $this->tableSelect->bindValues($expect);
        $actual = $this->tableSelect->getBindValues();
        $this->assertSame($expect, $actual);
    }

    public function testFetchAssoc()
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

        $actual = $this->tableSelect
            ->cols(['*'])
            ->where('id <= ?', 3)
            ->fetchAssoc();

        $this->assertSame($expect, $actual);
    }

    public function testFetchCol()
    {
        $expect = ['Anna', 'Betty', 'Clara'];

        $actual = $this->tableSelect
            ->cols(['name'])
            ->where('id <= ?', 3)
            ->fetchCol();

        $this->assertSame($expect, $actual);
    }

    public function testFetchPairs()
    {
        $expect = [
            '1' => 'Anna',
            '2' => 'Betty',
            '3' => 'Clara',
        ];

        $actual = $this->tableSelect
            ->cols(['id', 'name'])
            ->where('id <= ?', 3)
            ->fetchPairs();

        $this->assertSame($expect, $actual);
    }

    public function testFetchValue()
    {
        $expect = 'Clara';
        $actual = $this->tableSelect
            ->cols(['name'])
            ->where('id = ?', 3)
            ->fetchValue();

        $this->assertSame($expect, $actual);
    }
}
