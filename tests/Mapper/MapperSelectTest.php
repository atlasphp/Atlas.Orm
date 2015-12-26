<?php
namespace Atlas\Orm\Mapper;

use Atlas\Orm\Assertions;
use Atlas\Orm\DataSource\Employee\EmployeeTable;
use Atlas\Orm\DataSource\Employee\EmployeeTableEvents;
use Atlas\Orm\SqliteFixture;
use Aura\Sql\ConnectionLocator;
use Aura\Sql\ExtendedPdo;
use Aura\SqlQuery\QueryFactory;

class SelectTest extends \PHPUnit_Framework_TestCase
{
    use Assertions;

    protected $mapperSelect;

    protected function setUp()
    {
        $connection = new ExtendedPdo('sqlite::memory:');

        $fixture = new SqliteFixture($connection);
        $fixture->exec();

        $queryFactory = new QueryFactory('sqlite');
        $select = $queryFactory->newSelect();
        $select->from('employee');

        $this->mapperSelect = new Select(
            $select,
            $connection,
            ['id', 'name', 'building', 'floor'],
            function () { },
            function () { },
            function () { },
            function () { }
        );
    }

    public function testGetStatement()
    {
        $this->mapperSelect->cols(['*']);
        $expect = '
            SELECT
                *
            FROM
                "employee"
        ';
        $actual = $this->mapperSelect->getStatement();
        $this->assertSameSql($expect, $actual);
    }

    public function testGetBindValues()
    {
        $expect = ['foo' => 'bar'];
        $this->mapperSelect->bindValues($expect);
        $actual = $this->mapperSelect->getBindValues();
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

        $actual = $this->mapperSelect
            ->cols(['*'])
            ->where('id <= ?', 3)
            ->fetchAssoc();

        $this->assertSame($expect, $actual);
    }

    public function testFetchCol()
    {
        $expect = ['Anna', 'Betty', 'Clara'];

        $actual = $this->mapperSelect
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

        $actual = $this->mapperSelect
            ->cols(['id', 'name'])
            ->where('id <= ?', 3)
            ->fetchPairs();

        $this->assertSame($expect, $actual);
    }

    public function testFetchValue()
    {
        $expect = 'Clara';
        $actual = $this->mapperSelect
            ->cols(['name'])
            ->where('id = ?', 3)
            ->fetchValue();

        $this->assertSame($expect, $actual);
    }
}
