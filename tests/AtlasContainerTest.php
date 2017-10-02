<?php
namespace Atlas\Orm;

use Atlas\Orm\Exception;
use Atlas\Orm\DataSource\Author\AuthorMapper;
use Atlas\Orm\DataSource\Author\AuthorTableEvents;
use Atlas\Orm\DataSource\Reply\ReplyMapper;
use Atlas\Orm\DataSource\Summary\SummaryMapper;
use Atlas\Orm\DataSource\Summary\SummaryTable;
use Atlas\Orm\DataSource\Tag\TagMapper;
use Atlas\Orm\DataSource\Thread\ThreadMapper;
use Atlas\Orm\DataSource\Tagging\TaggingMapper;
use Aura\Sql\ConnectionLocator;
use Aura\Sql\ExtendedPdo;
use PDO;

class AtlasContainerTest extends \PHPUnit\Framework\TestCase
{
    protected $atlasContainer;

    protected function setUp()
    {
        $this->atlasContainer = new AtlasContainer('sqlite::memory:');
    }

    public function testMapperWithoutTable()
    {
        $this->expectException(
            Exception::CLASS,
            'Atlas\Orm\Fake\FakeMapperWithouTable does not exist.'
        );

        $this->atlasContainer->setMappers([
            'Atlas\Orm\Fake\FakeMapperWithoutTable'
        ]);
    }

    public function test()
    {
        // mappers
        $this->atlasContainer->setMappers([
            AuthorMapper::CLASS,
            ReplyMapper::CLASS,
            SummaryMapper::CLASS,
            TagMapper::CLASS,
            ThreadMapper::CLASS,
            TaggingMapper::CLASS,
        ]);

        // fake a special factory for a row filter
        $this->atlasContainer->setFactoryFor(AuthorTableEvents::CLASS, function () {
            return new AuthorTableEvents();
        });

        // get the atlas
        $atlas = $this->atlasContainer->getAtlas();

        // check that the mappers instantiated
        $this->assertInstanceOf(AuthorMapper::CLASS, $atlas->mapper(AuthorMapper::CLASS));
        $this->assertInstanceOf(ReplyMapper::CLASS, $atlas->mapper(ReplyMapper::CLASS));
        $this->assertInstanceOf(SummaryMapper::CLASS, $atlas->mapper(SummaryMapper::CLASS));
        $this->assertInstanceOf(TagMapper::CLASS, $atlas->mapper(TagMapper::CLASS));
        $this->assertInstanceOf(ThreadMapper::CLASS, $atlas->mapper(ThreadMapper::CLASS));
        $this->assertInstanceOf(TaggingMapper::CLASS, $atlas->mapper(TaggingMapper::CLASS));
    }

    public function testSetMapper_noSuchMapper()
    {
        $this->expectException(
            Exception::CLASS,
            'FooMapper does not exist'
        );
        $this->atlasContainer->setMapper('FooMapper');
    }

    public function testConstructWithConnectionLocator()
    {
        $locator = new ConnectionLocator(function() {
            return new ExtendedPdo('sqlite::memory:');
        });
        $atlasContainer = new AtlasContainer($locator);

        $this->assertEquals($locator, $atlasContainer->getConnectionLocator());
    }

    public function testConstructWithExtendedPdo()
    {
        $extendedPdo = new ExtendedPdo('sqlite::memory:');
        $atlasContainer = new AtlasContainer($extendedPdo);
        $actual = $atlasContainer->getConnectionLocator()->getDefault();
        $this->assertSame($extendedPdo, $actual);
    }

    public function testConstructWithPdo()
    {
        $pdo = new Pdo('sqlite::memory:');
        $atlasContainer = new AtlasContainer($pdo);
        $actual = $atlasContainer->getConnectionLocator()->getDefault()->getPdo();
        $this->assertSame($pdo, $actual);
    }

    public function testCustomConnections()
    {
        $conn1 = new ExtendedPdo('sqlite::memory:');
        $conn2 = new ExtendedPdo('sqlite::memory:');
        $conn3 = new ExtendedPdo('sqlite::memory:');
        $conn4 = new ExtendedPdo('sqlite::memory:');

        $this->atlasContainer->setReadConnection('r1', function () use ($conn1) { return $conn1; });
        $this->atlasContainer->setReadConnection('r2', function () use ($conn2) { return $conn2; });
        $this->atlasContainer->setWriteConnection('w1', function () use ($conn3) { return $conn3; });
        $this->atlasContainer->setWriteConnection('w2', function () use ($conn4) { return $conn4; });

        $this->atlasContainer->setReadConnectionForTable('Foo', 'r2');
        $this->atlasContainer->setWriteConnectionForTable('Foo', 'w2');

        $connectionManager = $this->atlasContainer->getConnectionManager();

        $actual = $connectionManager->getRead('Foo');
        $this->assertSame($conn2, $actual);

        $actual = $connectionManager->getWrite('Foo');
        $this->assertSame($conn4, $actual);
    }

    public function testSetReadFromWrite()
    {
        $connectionManager = $this->atlasContainer->getConnectionManager();
        $this->assertSame($connectionManager::NEVER, $connectionManager->getReadFromWrite());

        $this->atlasContainer->setReadFromWrite($connectionManager::ALWAYS);
        $this->assertSame($connectionManager::ALWAYS, $connectionManager->getReadFromWrite());
    }
}
