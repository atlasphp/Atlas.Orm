<?php
namespace Atlas;

use Atlas\Exception;
use Atlas\Fake\Author\AuthorMapper;
use Atlas\Fake\Author\AuthorRowFilter;
use Atlas\Fake\Reply\ReplyMapper;
use Atlas\Fake\Summary\SummaryMapper;
use Atlas\Fake\Summary\SummaryTable;
use Atlas\Fake\Tag\TagMapper;
use Atlas\Fake\Thread\ThreadMapper;
use Atlas\Fake\Tagging\TaggingMapper;
use Aura\Sql\ExtendedPdo;

class AtlasContainerTest extends \PHPUnit_Framework_TestCase
{
    protected $atlasContainer;

    protected function setUp()
    {
        $this->atlasContainer = new AtlasContainer('sqlite');
    }

    public function test()
    {
        // set all connections for coverage
        $connection = new ExtendedPdo('sqlite::memory:');
        $this->atlasContainer->setDefaultConnection(function () use ($connection) {
            return $connection;
        });
        $this->atlasContainer->setReadConnection('default', function () use ($connection) {
            return $connection;
        });
        $this->atlasContainer->setWriteConnection('default', function () use ($connection) {
            return $connection;
        });

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
        $this->atlasContainer->setFactoryFor(AuthorRowFilter::CLASS, function () {
            return new AuthorRowFilter();
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

    public function testSetMapper_noSuchTable()
    {
        $this->setExpectedException(
            Exception::CLASS,
            'FooTable does not exist'
        );
        $this->atlasContainer->setMapper(AuthorMapper::CLASS, 'FooTable');
    }
}
