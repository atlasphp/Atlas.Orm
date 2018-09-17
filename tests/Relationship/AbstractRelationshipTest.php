<?php
namespace Atlas\Orm\Relationship;

use Atlas\Orm\AtlasBuilder;
use Atlas\Orm\Mapper\MapperLocator;
use Aura\Sql\ExtendedPdo;
use Atlas\Orm\SqliteFixture;

abstract class AbstractRelationshipTest extends \PHPUnit\Framework\TestCase
{
    protected $mapperLocator;

    protected function setUp()
    {
        $atlasBuilder = new AtlasBuilder('sqlite::memory:');

        $connection = $atlasBuilder->getConnectionLocator()->getDefault();
        $fixture = new SqliteFixture($connection);
        $fixture->exec();

        $this->mapperLocator = $atlasBuilder->newAtlas()->getMapperLocator();
    }
}
