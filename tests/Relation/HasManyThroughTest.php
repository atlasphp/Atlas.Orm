<?php
namespace Atlas\Relation;

use Atlas\Exception;
use Atlas\DataSource\Tag\TagMapper;
use Atlas\DataSource\Tagging\TaggingMapper;
use Atlas\DataSource\Thread\ThreadMapper;

class HasManyThroughTest extends AbstractRelationTest
{
    public function testCustomSettings()
    {
        $rel = new HasManyThrough(
            $this->mapperLocator,
            ThreadMapper::CLASS,
            'threads',
            TagMapper::CLASS,
            'taggings'
        );

        $rel->nativeCol('native')
            ->throughNativeCol('through_native')
            ->throughForeignCol('through_foreign')
            ->foreignCol('foreign');

        $expect = [
            'name' => 'threads',
            'nativeMapperClass' => 'Atlas\\DataSource\\Thread\\ThreadMapper',
            'foreignMapperClass' => 'Atlas\\DataSource\\Tag\\TagMapper',
            'nativeCol' => 'native',
            'throughName' => 'taggings',
            'throughNativeCol' => 'through_native',
            'throughForeignCol' => 'through_foreign',
            'foreignCol' => 'foreign',
        ];

        $actual = $rel->getSettings();
        $this->assertSame($expect, $actual);

        // get them again, make sure they stay fixed
        $actual = $rel->getSettings();
        $this->assertSame($expect, $actual);
    }

    public function testStitchIntoRecord_missingThrough()
    {
        $rel = new HasManyThrough(
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
        $rel = new HasManyThrough(
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
        $rel = new HasManyThrough(
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
        $rel = new HasManyThrough(
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
