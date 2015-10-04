<?php
namespace Atlas\Relationship;

use Atlas\Fake\Author\AuthorMapper;
use Atlas\Fake\Thread\ThreadMapper;

class BelongsToTest extends AbstractRelationshipTest
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
            'nativeMapperClass' => 'Atlas\\Fake\\Thread\\ThreadMapper',
            'foreignMapperClass' => 'Atlas\\Fake\\Author\\AuthorMapper',
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
