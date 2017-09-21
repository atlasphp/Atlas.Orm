<?php
namespace Atlas\Orm\Mapper;

use Atlas\Orm\Assertions;
use Atlas\Orm\AtlasContainer;
use Atlas\Orm\DataSource\Employee\EmployeeMapper;
use Atlas\Orm\SqliteFixture;
use Atlas\Orm\Table\IdentityMap;
use Atlas\Orm\Table\TableEvents;
use Atlas\Orm\Table\TableSelect;
use Aura\Sql\ConnectionLocator;
use Aura\Sql\ExtendedPdo;
use Aura\SqlQuery\QueryFactory;
use Iterator;

class MapperSelectTest extends \PHPUnit\Framework\TestCase
{
    use Assertions;

    protected $select;

    protected function setUp()
    {
        $atlasContainer = new AtlasContainer('sqlite::memory:');
        $atlasContainer->setMappers([
            EmployeeMapper::CLASS,
        ]);

        $connection = $atlasContainer->getConnectionLocator()->getDefault();
        $fixture = new SqliteFixture($connection);
        $fixture->exec();

        $this->select = $atlasContainer
            ->getMapperLocator()
            ->get(EmployeeMapper::CLASS)
            ->select();
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
                "employee"."id",
                "employee"."name",
                "employee"."building",
                "employee"."floor"
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
                "employee"."id",
                "employee"."name",
                "employee"."building",
                "employee"."floor"
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

    public function testFetchCount()
    {
        $expect = [
            '1' => 'Anna',
            '2' => 'Betty',
            '3' => 'Clara',
        ];

        $actual = $this->select
            ->cols(['id', 'name'])
            ->where('id > 0')
            ->page(1)
            ->setPaging(3)
            ->fetchPairs();

        $this->assertSame($expect, $actual);
        $expect_select = $this->select->getStatement();

        $expect = 12;
        $actual = $this->select->fetchCount();
        $this->assertEquals($expect, $actual);

        // make sure it cloned properly inside Select
        $actual_select = $this->select->getStatement();
        $this->assertSame($expect_select, $actual_select);
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

    public function testWith_noSuchRelationship()
    {
        $this->expectException(
            'Atlas\Orm\Exception',
            "Relationship 'no_such_related' does not exist."
        );
        $this->select->with(['no_such_related']);
    }
}
