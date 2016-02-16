<?php
namespace Atlas\Orm\Relationship;

use Atlas\Orm\Exception;
use Atlas\Orm\DataSource\Tag\TagMapper;
use Atlas\Orm\DataSource\Tagging\TaggingMapper;
use Atlas\Orm\DataSource\Thread\ThreadMapper;

class ManyToManyTest extends AbstractRelationshipTest
{
    public function testCustomSettings()
    {
        $rel = new ManyToMany(
            $this->mapperLocator,
            ThreadMapper::CLASS,
            'threads',
            TagMapper::CLASS,
            'taggings'
        );

        $rel->on(['through_foreign' => 'foreign']);

        $expect = [
            'name' => 'threads',
            'nativeMapperClass' => 'Atlas\\Orm\\DataSource\\Thread\\ThreadMapper',
            'foreignMapperClass' => 'Atlas\\Orm\\DataSource\\Tag\\TagMapper',
            'foreignTable' => 'tags',
            'on' => ['through_foreign' => 'foreign'],
            'throughName' => 'taggings',
        ];

        $actual = $rel->getSettings();
        $this->assertSame($expect, $actual);

        // get them again, make sure they stay fixed
        $actual = $rel->getSettings();
        $this->assertSame($expect, $actual);
    }

    public function testStitchIntoRecord_missingThrough()
    {
        $rel = new ManyToMany(
            $this->mapperLocator,
            TagMapper::CLASS,
            'tags',
            TagMapper::CLASS,
            'taggings'
        );

        $thread = $this->mapperLocator->get(ThreadMapper::CLASS)->newRecord([]);

        $this->setExpectedException(
            Exception::CLASS,
            "Cannot fetch 'tags' relation without 'taggings'"
        );
        $rel->stitchIntoRecord($thread);
    }

    public function testStitchIntoRecordSet_emptyNativeRecordSet()
    {
        $rel = new ManyToMany(
            $this->mapperLocator,
            TagMapper::CLASS,
            'tags',
            TagMapper::CLASS,
            'taggings'
        );

        $threads = $this->mapperLocator->get(ThreadMapper::CLASS)->newRecordSet();
        $rel->stitchIntoRecordSet($threads);

        $this->assertTrue($threads->isEmpty());
    }

    public function testStitchIntoRecordSet_missingThrough()
    {
        $rel = new ManyToMany(
            $this->mapperLocator,
            TagMapper::CLASS,
            'tags',
            TagMapper::CLASS,
            'taggings'
        );

        $threadMapper = $this->mapperLocator->get(ThreadMapper::CLASS);
        $thread = $threadMapper->newRecord();
        $threads = $threadMapper->newRecordSet([$thread]);

        $this->setExpectedException(
            Exception::CLASS,
            "Cannot fetch 'tags' relation without 'taggings'"
        );
        $rel->stitchIntoRecordSet($threads);
    }

    public function testStitchIntoRecordSet_emptyThrough()
    {
        $rel = new ManyToMany(
            $this->mapperLocator,
            TagMapper::CLASS,
            'tags',
            TagMapper::CLASS,
            'taggings'
        );

        $threadMapper = $this->mapperLocator->get(ThreadMapper::CLASS);
        $thread = $threadMapper->newRecord();

        $taggingMapper = $this->mapperLocator->get(TaggingMapper::CLASS);
        $thread->taggings = $taggingMapper->newRecordSet();

        $threads = $threadMapper->newRecordSet([$thread]);

        $rel->stitchIntoRecordSet($threads);

        $this->assertSame([], $thread->tags);
    }
}
