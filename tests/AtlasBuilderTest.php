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

class AtlasBuilderTest extends AtlasContainerTest
{
    protected function newAtlasContainer(...$args)
    {
        return new AtlasBuilder(...$args);
    }

    public function testMapperWithoutTable()
    {
        $atlas = $this->atlasContainer->newAtlas();
        $this->expectException(Exception::CLASS);
        $this->expectExceptionMessage('Atlas\Orm\Fake\FakeTablelessTable does not exist.');
        $atlas->mapper('Atlas\Orm\Fake\FakeTablelessMapper');
    }

    public function test()
    {
        $atlas = $this->atlasContainer->newAtlas();
        $this->assertInstanceOf(AuthorMapper::CLASS, $atlas->mapper(AuthorMapper::CLASS));
        $this->assertInstanceOf(ReplyMapper::CLASS, $atlas->mapper(ReplyMapper::CLASS));
        $this->assertInstanceOf(SummaryMapper::CLASS, $atlas->mapper(SummaryMapper::CLASS));
        $this->assertInstanceOf(TagMapper::CLASS, $atlas->mapper(TagMapper::CLASS));
        $this->assertInstanceOf(ThreadMapper::CLASS, $atlas->mapper(ThreadMapper::CLASS));
        $this->assertInstanceOf(TaggingMapper::CLASS, $atlas->mapper(TaggingMapper::CLASS));
    }

    public function testSetMapper_noSuchMapper()
    {
        $atlas = $this->atlasContainer->newAtlas();
        $this->expectException(Exception::CLASS);
        $this->expectExceptionMessage('FooMapper not found in mapper locator.');
        $atlas->mapper('FooMapper');
    }
}
