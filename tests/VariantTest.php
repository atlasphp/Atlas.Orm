<?php
namespace Atlas\Orm;

use Atlas\Orm\DataSource\Page\PageMapper;
use Atlas\Orm\DataSource\Page\PageRecord;
use Atlas\Orm\DataSource\Post\PostMapper;
use Atlas\Orm\DataSource\Post\PostRecord;
use Atlas\Orm\DataSource\Video\VideoMapper;
use Atlas\Orm\DataSource\Video\VideoRecord;
use Atlas\Orm\DataSource\Comment\CommentMapper;
use Atlas\Orm\DataSource\Comment\CommentRecord;
use Atlas\Orm\Mapper\Record;
use Atlas\Orm\Mapper\RecordSet;
use Aura\Sql\ExtendedPdo;
use Aura\Sql\Profiler;

class VariantTest extends \PHPUnit\Framework\TestCase
{
    use Assertions;

    protected $atlas;

    protected function setUp()
    {
        $atlasContainer = new AtlasContainer('sqlite::memory:');
        $atlasContainer->setMappers([
            CommentMapper::CLASS,
            PageMapper::CLASS,
            PostMapper::CLASS,
            VideoMapper::CLASS,
        ]);

        $connection = $atlasContainer->getConnectionLocator()->getDefault();
        $fixture = new SqliteFixture($connection);
        $fixture->exec();

        $this->atlas = $atlasContainer->getAtlas();
    }

    public function testVariantFetch()
    {
        $comments = $this->atlas
            ->select(CommentMapper::CLASS)
            ->orderBy(['comment_id'])
            ->with(['commentable'])
            ->limit(3)
            ->fetchRecords();

        $this->assertInstanceOf(PageRecord::CLASS, $comments[0]->commentable);
        $this->assertInstanceOf(PostRecord::CLASS, $comments[1]->commentable);
        $this->assertInstanceOf(VideoRecord::CLASS, $comments[2]->commentable);
    }

    public function testVariantPersist()
    {
        $this->markTestIncomplete();
    }
}
