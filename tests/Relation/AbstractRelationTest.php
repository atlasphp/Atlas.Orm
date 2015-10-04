<?php
namespace Atlas\Relation;

use Atlas\AtlasContainer;
use Atlas\Fake\Author\AuthorMapper;
use Atlas\Fake\Reply\ReplyMapper;
use Atlas\Fake\Summary\SummaryMapper;
use Atlas\Fake\Summary\SummaryTable;
use Atlas\Fake\Tag\TagMapper;
use Atlas\Fake\Tagging\TaggingMapper;
use Atlas\Fake\Thread\ThreadMapper;
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
