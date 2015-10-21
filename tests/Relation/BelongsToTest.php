<?php
namespace Atlas\Relation;

use Atlas\DataSource\Author\AuthorMapper;
use Atlas\DataSource\Thread\ThreadMapper;

class BelongsToTest extends AbstractRelationTest
{
    public function testCustomSettings()
    {
        $rel = new BelongsTo(
            $this->mapperLocator,
            ThreadMapper::CLASS,
            'author',
            AuthorMapper::CLASS
        );

        $rel->nativeCol('native')
            ->foreignCol('foreign');

        $expect = [
            'name' => 'author',
            'nativeMapperClass' => 'Atlas\\DataSource\\Thread\\ThreadMapper',
            'foreignMapperClass' => 'Atlas\\DataSource\\Author\\AuthorMapper',
            'nativeCol' => 'native',
            'throughName' => null,
            'throughNativeCol' => null,
            'throughForeignCol' => null,
            'foreignCol' => 'foreign',
        ];

        $actual = $rel->getSettings();
        $this->assertSame($expect, $actual);

        // get them again, make sure they stay fixed
        $actual = $rel->getSettings();
        $this->assertSame($expect, $actual);
    }
}
