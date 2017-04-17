<?php
namespace Atlas\Orm\Relationship;

use Atlas\Orm\DataSource\Author\AuthorMapper;
use Atlas\Orm\DataSource\Thread\ThreadMapper;

class ManyToOneTest extends AbstractRelationshipTest
{
    public function testCustomSettings()
    {
        $rel = new ManyToOne(
            'author',
            $this->mapperLocator,
            ThreadMapper::CLASS,
            AuthorMapper::CLASS
        );

        $rel->on(['native' => 'foreign']);

        $expect = [
            'name' => 'author',
            'nativeMapperClass' => 'Atlas\\Orm\\DataSource\\Thread\\ThreadMapper',
            'foreignMapperClass' => 'Atlas\\Orm\\DataSource\\Author\\AuthorMapper',
            'foreignTableName' => 'authors',
            'on' => ['native' => 'foreign'],
            'ignoreCase' => false,
            'where' => [],
            'throughName' => null,
        ];

        $actual = $rel->getSettings();
        $this->assertSame($expect, $actual);

        // get them again, make sure they stay fixed
        $actual = $rel->getSettings();
        $this->assertSame($expect, $actual);
    }
}
