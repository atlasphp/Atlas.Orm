<?php
namespace Atlas\Orm\Relation;

use Atlas\Orm\AtlasContainer;
use Atlas\Orm\DataSource\Author\AuthorMapper;
use Atlas\Orm\DataSource\Reply\ReplyMapper;
use Atlas\Orm\DataSource\Summary\SummaryMapper;
use Atlas\Orm\DataSource\Summary\SummaryTable;
use Atlas\Orm\DataSource\Tag\TagMapper;
use Atlas\Orm\DataSource\Tagging\TaggingMapper;
use Atlas\Orm\DataSource\Thread\ThreadMapper;
use Atlas\Orm\Mapper\MapperLocator;
use Aura\Sql\ExtendedPdo;
use Atlas\Orm\SqliteFixture;

abstract class AbstractRelationTest extends \PHPUnit_Framework_TestCase
{
    protected $mapperLocator;

    protected function setUp()
    {
        $connection = new ExtendedPdo('sqlite::memory:');

        $atlasContainer = new AtlasContainer('sqlite');

        $atlasContainer->setDefaultConnection(function () use ($connection) {
            return $connection;
        });

        $atlasContainer->setMappers([
            AuthorMapper::CLASS,
            ReplyMapper::CLASS,
            SummaryMapper::CLASS => SummaryTable::CLASS,
            TagMapper::CLASS,
            ThreadMapper::CLASS,
            TaggingMapper::CLASS,
        ]);

        $this->mapperLocator = $atlasContainer->getMapperLocator();

        $fixture = new SqliteFixture($connection);
        $fixture->exec();
    }
}
