<?php
namespace Atlas\Orm\Relationship;

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

abstract class AbstractRelationshipTest extends \PHPUnit\Framework\TestCase
{
    protected $mapperLocator;

    protected function setUp()
    {
        $atlasContainer = new AtlasContainer('sqlite::memory:');

        $atlasContainer->setMappers([
            AuthorMapper::CLASS,
            ReplyMapper::CLASS,
            SummaryMapper::CLASS,
            TagMapper::CLASS,
            ThreadMapper::CLASS,
            TaggingMapper::CLASS,
        ]);

        $this->mapperLocator = $atlasContainer->getMapperLocator();

        $connection = $atlasContainer->getConnectionLocator()->getDefault();
        $fixture = new SqliteFixture($connection);
        $fixture->exec();
    }
}
