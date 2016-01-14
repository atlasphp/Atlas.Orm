<?php
namespace Atlas\Orm\Relationship;

use Atlas\Orm\DataSource\Summary\SummaryMapper;
use Atlas\Orm\DataSource\Thread\ThreadMapper;

class OneToOneTest extends AbstractRelationshipTest
{
    public function testCustomSettings()
    {
        $rel = new OneToOne(
            $this->mapperLocator,
            ThreadMapper::CLASS,
            'summary',
            SummaryMapper::CLASS
        );

        $rel->nativeKey('native')
            ->foreignKey('foreign');

        $expect = [
            'name' => 'summary',
            'nativeMapperClass' => 'Atlas\\Orm\\DataSource\\Thread\\ThreadMapper',
            'foreignMapperClass' => 'Atlas\\Orm\\DataSource\\Summary\\SummaryMapper',
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
