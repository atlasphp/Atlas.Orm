<?php
namespace Atlas\Relation;

use Atlas\Fake\Summary\SummaryMapper;
use Atlas\Fake\Thread\ThreadMapper;

class HasOneTest extends AbstractRelationTest
{
    public function testCustomSettings()
    {
        $rel = new HasOne(
            $this->mapperLocator,
            ThreadMapper::CLASS,
            'summary',
            SummaryMapper::CLASS
        );

        $rel->nativeCol('native')
            ->foreignCol('foreign');

        $expect = [
            'name' => 'summary',
            'nativeMapperClass' => 'Atlas\\Fake\\Thread\\ThreadMapper',
            'foreignMapperClass' => 'Atlas\\Fake\\Summary\\SummaryMapper',
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
