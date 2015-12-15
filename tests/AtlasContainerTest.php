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
use Aura\Sql\ExtendedPdo;

class AtlasContainerTest extends \PHPUnit_Framework_TestCase
{
    protected $atlasContainer;

    protected function setUp()
    {
        $this->atlasContainer = new AtlasContainer('sqlite::memory:');
    }

    public function test()
    {
        // mappers
        $this->atlasContainer->setMappers([
            AuthorMapper::CLASS,
            ReplyMapper::CLASS,
            SummaryMapper::CLASS => SummaryTable::CLASS,
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
        $this->setExpectedException(
            Exception::CLASS,
            'FooMapper does not exist'
        );
        $this->atlasContainer->setMapper('FooMapper');
    }

    public function testSetTable_noSuchTable()
    {
        $this->setExpectedException(
            Exception::CLASS,
            'FooTableGateway does not exist'
        );
        $this->atlasContainer->setTable('FooTable');
    }
}
