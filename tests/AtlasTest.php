<?php
namespace Atlas;

use Atlas\Fake\Author\AuthorMapper;
use Atlas\Fake\Reply\ReplyMapper;
use Atlas\Fake\Summary\SummaryMapper;
use Atlas\Fake\Summary\SummaryTable;
use Atlas\Fake\Tag\TagMapper;
use Atlas\Fake\Thread\ThreadMapper;
use Atlas\Fake\Thread2Tag\Thread2TagMapper;
use Aura\Sql\ExtendedPdo;

class AtlasTest extends \PHPUnit_Framework_TestCase
{
    protected $atlas;

    protected function setUp()
    {
        $atlasContainer = new AtlasContainer('sqlite');
        $atlasContainer->setDefaultConnection(function () {
            return new ExtendedPdo('sqlite::memory:');
        });
        $atlasContainer->setMappers([
            AuthorMapper::CLASS,
            ReplyMapper::CLASS,
            SummaryMapper::CLASS,
            TagMapper::CLASS,
            ThreadMapper::CLASS,
            Thread2TagMapper::CLASS,
        ]);
        $this->atlas = $atlasContainer->getAtlas();
    }

    public function testSelect()
    {
        $select = $this->atlas->select(ThreadMapper::CLASS);
        $this->assertInstanceOf('Atlas\AtlasSelect', $select);
    }
}
