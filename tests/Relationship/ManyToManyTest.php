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
            'threads',
            $this->mapperLocator,
            ThreadMapper::CLASS,
            TagMapper::CLASS,
            'taggings'
        );

        $rel->on(['through_foreign' => 'foreign']);

        $expect = [
            'name' => 'threads',
            'nativeMapperClass' => 'Atlas\\Orm\\DataSource\\Thread\\ThreadMapper',
            'foreignMapperClass' => 'Atlas\\Orm\\DataSource\\Tag\\TagMapper',
            'foreignTableName' => 'tags',
            'on' => ['through_foreign' => 'foreign'],
            'ignoreCase' => false,
            'where' => [],
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
            'tags',
            $this->mapperLocator,
            TagMapper::CLASS,
            TagMapper::CLASS,
            'taggings'
        );

        $thread = $this->mapperLocator->get(ThreadMapper::CLASS)->newRecord([]);

        $this->expectException(
            Exception::CLASS,
            "Cannot fetch 'tags' relationship without 'taggings' relationship."
        );
        $rel->stitchIntoRecords([$thread]);
    }

    public function testStitchIntoRecords_emptyNativeRecords()
    {
        $rel = new ManyToMany(
            'tags',
            $this->mapperLocator,
            TagMapper::CLASS,
            TagMapper::CLASS,
            'taggings'
        );

        $threads = [];
        $rel->stitchIntoRecords($threads);

        $this->assertTrue(empty($threads));
    }

    public function testStitchIntoRecords_missingThrough()
    {
        $rel = new ManyToMany(
            'tags',
            $this->mapperLocator,
            TagMapper::CLASS,
            TagMapper::CLASS,
            'taggings'
        );

        $threadMapper = $this->mapperLocator->get(ThreadMapper::CLASS);
        $thread = $threadMapper->newRecord();
        $threads = [$thread];

        $this->expectException(
            Exception::CLASS,
            "Cannot fetch 'tags' relationship without 'taggings' relationship."
        );
        $rel->stitchIntoRecords($threads);
    }

    public function testStitchIntoRecords_emptyThrough()
    {
        $rel = new ManyToMany(
            'tags',
            $this->mapperLocator,
            TagMapper::CLASS,
            TagMapper::CLASS,
            'taggings'
        );

        $threadMapper = $this->mapperLocator->get(ThreadMapper::CLASS);
        $thread = $threadMapper->newRecord();

        $taggingMapper = $this->mapperLocator->get(TaggingMapper::CLASS);
        $thread->taggings = $taggingMapper->newRecordSet();

        $threads = [$thread];

        $rel->stitchIntoRecords($threads);

        $this->assertSame([], $thread->tags);
    }
}
