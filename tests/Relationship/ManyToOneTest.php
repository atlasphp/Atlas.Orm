<?php
namespace Atlas\Orm\Relationship;

use Atlas\Orm\DataSource\Author\AuthorMapper;
use Atlas\Orm\DataSource\Thread\ThreadMapper;

class ManyToOneTest extends AbstractRelationshipTest
{
    public function testCustomSettings()
    {
        $rel = new ManyToOne(
            $this->mapperLocator,
            ThreadMapper::CLASS,
            'author',
            AuthorMapper::CLASS
        );

        $rel->on(['native' => 'foreign']);

        $expect = [
            'name' => 'author',
            'nativeMapperClass' => 'Atlas\\Orm\\DataSource\\Thread\\ThreadMapper',
            'foreignMapperClass' => 'Atlas\\Orm\\DataSource\\Author\\AuthorMapper',
            'on' => ['native' => 'foreign'],
            'throughName' => null,
        ];

        $actual = $rel->getSettings();
        $this->assertSame($expect, $actual);

        // get them again, make sure they stay fixed
        $actual = $rel->getSettings();
        $this->assertSame($expect, $actual);
    }
}
