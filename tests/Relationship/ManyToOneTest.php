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

        $rel->nativeKey('native')
            ->foreignKey('foreign');

        $expect = [
            'name' => 'author',
            'nativeMapperClass' => 'Atlas\\Orm\\DataSource\\Thread\\ThreadMapper',
            'foreignMapperClass' => 'Atlas\\Orm\\DataSource\\Author\\AuthorMapper',
            'nativeKey' => 'native',
            'throughName' => null,
            'throughNativeKey' => null,
            'throughForeignKey' => null,
            'foreignKey' => 'foreign',
        ];

        $actual = $rel->getSettings();
        $this->assertSame($expect, $actual);

        // get them again, make sure they stay fixed
        $actual = $rel->getSettings();
        $this->assertSame($expect, $actual);
    }
}
