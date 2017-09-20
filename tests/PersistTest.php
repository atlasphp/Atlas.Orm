<?php
namespace Atlas\Orm;

use Atlas\Orm\DataSource\Author\AuthorMapper;
use Atlas\Orm\DataSource\Reply\ReplyMapper;
use Atlas\Orm\DataSource\Reply\ReplyRecord;
use Atlas\Orm\DataSource\Reply\ReplyRecordSet;
use Atlas\Orm\DataSource\Summary\SummaryMapper;
use Atlas\Orm\DataSource\Summary\SummaryTable;
use Atlas\Orm\DataSource\Tag\TagMapper;
use Atlas\Orm\DataSource\Tagging\TaggingMapper;
use Atlas\Orm\DataSource\Thread\ThreadMapper;
use Atlas\Orm\DataSource\Thread\ThreadRecord;
use Atlas\Orm\DataSource\Thread\ThreadRecordSet;
use Atlas\Orm\Mapper\Record;
use Atlas\Orm\Mapper\RecordSet;
use Aura\Sql\ExtendedPdo;
use Aura\Sql\Profiler;

class PersistTest extends \PHPUnit\Framework\TestCase
{
    use Assertions;

    protected $atlas;

    protected function setUp()
    {
        $atlasContainer = new AtlasContainer('sqlite::memory:');
        $atlasContainer->setMappers([
            AuthorMapper::CLASS,
            ReplyMapper::CLASS,
            SummaryMapper::CLASS,
            TagMapper::CLASS,
            ThreadMapper::CLASS,
            TaggingMapper::CLASS,
        ]);

        $connection = $atlasContainer->getConnectionLocator()->getDefault();
        $fixture = new SqliteFixture($connection);
        $fixture->exec();

        $this->atlas = $atlasContainer->getAtlas();
    }

    public function testAllNew()
    {
        $author = $this->atlas->newRecord(AuthorMapper::CLASS, [
            'name' => 'New Name',
        ]);

        $tag = $this->atlas->newRecord(TagMapper::CLASS, [
            'name' => 'New Tag',
        ]);

        $summary = $this->atlas->newRecord(SummaryMapper::CLASS, [
            'reply_count' => 0,
            'view_count' => 0,
        ]);

        $taggings = $this->atlas->newRecordSet(TaggingMapper::CLASS);

        $tags = $this->atlas->newRecordSet(TagMapper::CLASS);

        $thread = $this->atlas->newRecord(ThreadMapper::CLASS ,[
            'subject' => 'New Subject',
            'body' => 'New Body',
            'author' => $author,
            'summary' => $summary,
            'taggings' => $taggings,
            'tags' => $tags,
        ]);

        $tagging = $thread->taggings->appendNew([
            'thread' => $thread,
            'tag' => $tag,
        ]);

        $thread->tags[] = $tag; // essentially for convenience

        // persist the thread and all its relateds
        $this->atlas->persist($thread);

        $this->assertTrue($author->author_id > 0);
        $this->assertTrue($tag->tag_id > 0);
        $this->assertTrue($thread->thread_id > 0);
        $this->assertSame($thread->author_id, $thread->author->author_id);
        $this->assertSame($thread->thread_id, $thread->summary->thread_id);
        $this->assertSame($thread->taggings[0]->thread_id, $thread->thread_id);
        $this->assertSame($thread->taggings[0]->tag_id, $thread->tags[0]->tag_id);
    }

    public function testUpdateManyToOne()
    {
        $thread = $this->atlas->fetchRecord(ThreadMapper::CLASS, 1, ['author']);
        $this->assertEquals(1, $thread->author_id);

        $author = $this->atlas->fetchRecord(AuthorMapper::CLASS, 2);
        $thread->author = $author;

        $this->atlas->persist($thread);
        $this->assertEquals(2, $thread->author_id);
    }

    public function testUpdateOneToMany()
    {
        $author = $this->atlas->fetchRecord(AuthorMapper::CLASS, 1, ['threads']);
        foreach ($author->threads as $thread) {
            $this->assertEquals(1, $thread->author_id);
        }
        $count = count($author->threads);

        $thread = $this->atlas
            ->select(ThreadMapper::CLASS)
            ->where('author_id != 1')
            ->fetchRecord();

        $author->threads[] = $thread;

        $this->atlas->persist($author);
        $this->assertEquals($count + 1, count($author->threads));
        foreach ($author->threads as $thread) {
            $this->assertEquals(1, $thread->author_id);
        }
    }

    public function testUpdateOneToOne()
    {
        $thread = $this->atlas->fetchRecord(ThreadMapper::CLASS, 1, ['summary']);
        $this->assertEquals(1, $thread->summary->summary_id); // primary key
        $this->assertEquals(1, $thread->summary->thread_id); // foreign key

        $summary = $this->atlas
            ->select(SummaryMapper::CLASS)
            ->where('thread_id != 1')
            ->fetchRecord();

        $thread->summary = $summary;
        $this->atlas->persist($thread);
        $this->assertEquals(1, $thread->summary->thread_id); // foreign key
    }
}
