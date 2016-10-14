<?php
namespace Atlas\Orm\Mapper;

use Atlas\Orm\Assertions;
use Atlas\Orm\SqliteFixture;
use Atlas\Orm\Table\IdentityMap;
use Atlas\Orm\Table\TableEvents;
use Atlas\Orm\Table\TableSelect;
use Aura\Sql\ConnectionLocator;
use Aura\Sql\ExtendedPdo;
use Aura\SqlQuery\QueryFactory;
use Atlas\Orm\DataSource\Employee\EmployeeTable;

class MapperSelectTest extends \PHPUnit_Framework_TestCase
{
    use Assertions;

    protected $select;

    protected function setUp()
    {
        $queryFactory = new QueryFactory('sqlite');

        $table = new EmployeeTable(
            new ConnectionLocator(function () {
                return new ExtendedPdo('sqlite::memory:');
            }),
            $queryFactory,
            new IdentityMap(),
            new TableEvents()
        );

        $connection = $table->getWriteConnection();
        $fixture = new SqliteFixture($connection);
        $fixture->exec();

        $select = $queryFactory->newSelect()->from('employee');

        $this->select = new MapperSelect(
            $this->getMock('Atlas\Orm\Mapper\MapperInterface'),
            new TableSelect($table, $select)
        );
    }

    public function testGetStatement()
    {
        $this->select->cols(['*']);
        $expect = '
            SELECT
                *
            FROM
                "employee"
        ';
        $actual = $this->select->getStatement();
        $this->assertSameSql($expect, $actual);
    }

    public function testGetStatementWithOutColumnsPassed()
    {
        $expect = '
            SELECT
                id,
                name,
                building,
                floor
            FROM
                "employee"
        ';
        $actual = $this->select->getStatement();
        $this->assertSameSql($expect, $actual);
    }

    public function test__toString()
    {
        $this->select->cols(['*']);
        $expect = '
            SELECT
                *
            FROM
                "employee"
        ';
        $actual = $this->select->__toString();
        $this->assertSameSql($expect, $actual);
    }

    public function testWithOutColumnsPassed__toString()
    {
        $expect = '
            SELECT
                id,
                name,
                building,
                floor
            FROM
                "employee"
        ';
        $actual = $this->select->__toString();
        $this->assertSameSql($expect, $actual);
    }

    public function testGetBindValues()
    {
        $expect = ['foo' => 'bar'];
        $this->select->bindValues($expect);
        $actual = $this->select->getBindValues();
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

        $actual = $this->select
            ->cols(['*'])
            ->where('id <= ?', 3)
            ->fetchAssoc();

        $this->assertSame($expect, $actual);
    }

    public function testFetchCol()
    {
        $expect = ['Anna', 'Betty', 'Clara'];

        $actual = $this->select
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

        $actual = $this->select
            ->cols(['id', 'name'])
            ->where('id <= ?', 3)
            ->fetchPairs();

        $this->assertSame($expect, $actual);
    }

    public function testFetchValue()
    {
        $expect = 'Clara';
        $actual = $this->select
            ->cols(['name'])
            ->where('id = ?', 3)
            ->fetchValue();

        $this->assertSame($expect, $actual);
    }

    public function testFetchRecordGetStatement()
    {
        $expect = '
            SELECT
                id,
                name
            FROM
                "employee"
        ';

        $this->select
            ->cols(['id', 'name'])
            ->fetchRecord();

        $actual = $this->select->__toString();

        $this->assertSameSql($expect, $actual);
    }
}
