<?php
namespace Atlas\Relation;

use Atlas\AtlasContainer;
use Atlas\DataSource\Author\AuthorMapper;
use Atlas\DataSource\Reply\ReplyMapper;
use Atlas\DataSource\Summary\SummaryMapper;
use Atlas\DataSource\Summary\SummaryTable;
use Atlas\DataSource\Tag\TagMapper;
use Atlas\DataSource\Tagging\TaggingMapper;
use Atlas\DataSource\Thread\ThreadMapper;
use Atlas\Mapper\MapperLocator;
use Aura\Sql\ExtendedPdo;
use Atlas\SqliteFixture;

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
