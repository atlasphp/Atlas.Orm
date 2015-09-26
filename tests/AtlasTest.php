<?php
namespace Atlas;

use Atlas\Fake\Author\AuthorMapper;
use Atlas\Fake\Reply\ReplyMapper;
use Atlas\Fake\Summary\SummaryMapper;
use Atlas\Fake\Summary\SummaryTable;
use Atlas\Fake\Tag\TagMapper;
use Atlas\Fake\Thread2Tag\Thread2TagMapper;
use Atlas\Fake\Thread\ThreadMapper;
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

        $connection = $atlasContainer->getConnectionLocator()->getDefault();
        $fixture = new SqliteFixture($connection);
        $fixture->exec();

        $this->atlas = $atlasContainer->getAtlas();
    }

    public function testSelect()
    {
        $select = $this->atlas->select(ThreadMapper::CLASS);
        $this->assertInstanceOf('Atlas\AtlasSelect', $select);
    }

    public function testFetchRecord()
    {
        $thread = $this->atlas->fetchRecord(
            ThreadMapper::CLASS,
            1,
            [
            'author', // manyToOne
            'summary', // oneToOne
            'replies', // oneToMany
            'threads2tags', // oneToMany,
            'tags', // manyToMany
        ]);
        var_export($thread->getArrayCopy());
    }

    // public function testFetchRecordSet()
    // {
    //     $threads = $this->atlas->fetchRecordSet(
    //         ThreadMapper::CLASS,
    //         [1, 2, 3],
    //         [
    //             'author', // manyToOne
    //             'summary', // oneToOne
    //             'replies' => function ($select) {
    //                 $select->with(['author']);
    //             }, // oneToMany
    //             'threads2tags', // oneToMany,
    //             // 'tags', // manyToMany
    //         ]
    //     );
    //     var_export($threads->getArrayCopy());
    // }
}
