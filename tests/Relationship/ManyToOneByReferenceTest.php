<?php
namespace Atlas\Orm;

use Atlas\Orm\DataSource\Comment\CommentMapper;
use Atlas\Orm\DataSource\Comment\CommentRecord;
use Atlas\Orm\DataSource\Page\PageMapper;
use Atlas\Orm\DataSource\Page\PageRecord;
use Atlas\Orm\DataSource\Post\PostMapper;
use Atlas\Orm\DataSource\Post\PostRecord;
use Atlas\Orm\DataSource\Video\VideoMapper;
use Atlas\Orm\DataSource\Video\VideoRecord;
use Atlas\Orm\Exception;
use Atlas\Orm\Mapper\Record;
use Atlas\Orm\Mapper\RecordSet;
use Aura\Sql\ExtendedPdo;
use Aura\Sql\Profiler;

class ManyToOneByReferenceTest extends \PHPUnit\Framework\TestCase
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

    public function testFetchByReference()
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

    public function testInsertByReference()
    {
        $page = $this->atlas->fetchRecord(PageMapper::CLASS, 1, ['comments']);
        $comment = $page->comments->appendNew([
            'commentable' => $page,
            'body' => 'New comment on page',
        ]);

        $this->assertNull($comment->related_type);
        $this->assertNull($comment->related_id);
        $this->atlas->mapper(CommentMapper::CLASS)->insert($comment);
        $this->assertEquals('page', $comment->related_type);
        $this->assertEquals($page->page_id, $comment->related_id);
    }

    public function testPersistByReference()
    {
        $page = $this->atlas->fetchRecord(PageMapper::CLASS, 1, ['comments']);
        $comment = $page->comments->appendNew([
            'commentable' => $page,
            'body' => 'New comment on page',
        ]);

        $this->assertNull($comment->related_type);
        $this->assertNull($comment->related_id);
        $success = $this->atlas->persist($page);
        $this->assertTrue($success);
        $this->assertEquals('page', $comment->related_type);
        $this->assertEquals($page->page_id, $comment->related_id);
    }

    public function testPersistByReference_noSuchReferenceValue()
    {
        $page = $this->atlas->fetchRecord(PageMapper::CLASS, 1, ['comments']);
        $comment = $page->comments->appendNew([
            'related_type' => 'NO_SUCH_TYPE',
            'body' => 'New comment on page',
        ]);
        $success = $this->atlas->persist($page);
        $this->assertFalse($success);
    }

    public function testPersistByReference_noSuchReferenceMapper()
    {
        $page = $this->atlas->fetchRecord(PageMapper::CLASS, 1, ['comments']);
        $comment = $page->comments->appendNew([
            'commentable' => $this->atlas->newRecord(CommentMapper::CLASS),
            'body' => 'New comment on page',
        ]);
        $success = $this->atlas->persist($page);
        $this->assertFalse($success);
    }

    public function testOn()
    {
        $relationship = $this->atlas
            ->mapper(CommentMapper::CLASS)
            ->getRelationships()
            ->get('commentable');

        $this->expectException(Exception::CLASS);
        $relationship->on(['foo' => 'bar']);
    }

    public function testWhere()
    {
        $relationship = $this->atlas
            ->mapper(CommentMapper::CLASS)
            ->getRelationships()
            ->get('commentable');

        $this->expectException(Exception::CLASS);
        $relationship->where('foo = ?', 'bar');
    }

    public function testIgnoreCase()
    {
        $relationship = $this->atlas
            ->mapper(CommentMapper::CLASS)
            ->getRelationships()
            ->get('commentable');

        $this->expectException(Exception::CLASS);
        $relationship->ignoreCase();
    }

    public function testStitchIntoRecords_noNativeRecords()
    {
        $relationship = $this->atlas
            ->mapper(CommentMapper::CLASS)
            ->getRelationships()
            ->get('commentable');

        $nativeRecords = [];
        $relationship->stitchIntoRecords($nativeRecords);
        $this->assertSame([], $nativeRecords);
    }
}
