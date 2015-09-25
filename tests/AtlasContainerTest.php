<?php
namespace Atlas;

use Atlas\Fake\Author\AuthorMapper;
use Atlas\Fake\Reply\ReplyMapper;
use Atlas\Fake\Summary\SummaryMapper;
use Atlas\Fake\Summary\SummaryTable;
use Atlas\Fake\Tag\TagMapper;
use Atlas\Fake\Thread\ThreadMapper;
use Atlas\Fake\Thread2Tag\Thread2TagMapper;
use ExtendedPdo;

class AtlasContainerTest extends \PHPUnit_Framework_TestCase
{
    protected $atlasContainer;

    protected function setUp()
    {
        $this->atlasContainer = new AtlasContainer('sqlite');
    }

    public function test()
    {
        $this->assertInstanceOf(AtlasContainer::CLASS, $this->atlasContainer);

        $this->atlasContainer->setDefaultConnection(function () {
            return new ExtendedPdo('sqlite::memory:');
        });

        $this->atlasContainer->setMappers([
            AuthorMapper::CLASS,
            ReplyMapper::CLASS,
            SummaryMapper::CLASS => SummaryTable::CLASS,
            TagMapper::CLASS,
            ThreadMapper::CLASS,
            Thread2TagMapper::CLASS,
        ]);

        $atlas = $this->atlasContainer->getAtlas();

        $this->assertInstanceOf(AuthorMapper::CLASS, $atlas->mapper(AuthorMapper::CLASS));
        $this->assertInstanceOf(ReplyMapper::CLASS, $atlas->mapper(ReplyMapper::CLASS));
        $this->assertInstanceOf(SummaryMapper::CLASS, $atlas->mapper(SummaryMapper::CLASS));
        $this->assertInstanceOf(TagMapper::CLASS, $atlas->mapper(TagMapper::CLASS));
        $this->assertInstanceOf(ThreadMapper::CLASS, $atlas->mapper(ThreadMapper::CLASS));
        $this->assertInstanceOf(Thread2TagMapper::CLASS, $atlas->mapper(Thread2TagMapper::CLASS));
    }
}
