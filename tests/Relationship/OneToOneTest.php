<?php
namespace Atlas\Orm\Relationship;

use Atlas\Orm\DataSource\Summary\SummaryMapper;
use Atlas\Orm\DataSource\Thread\ThreadMapper;

class OneToOneTest extends AbstractRelationshipTest
{
    public function testCustomSettings()
    {
        $rel = new OneToOne(
            'summary',
            $this->mapperLocator,
            ThreadMapper::CLASS,
            SummaryMapper::CLASS
        );

        $rel->on(['native' => 'foreign']);

        $expect = [
            'name' => 'summary',
            'nativeMapperClass' => 'Atlas\\Orm\\DataSource\\Thread\\ThreadMapper',
            'foreignMapperClass' => 'Atlas\\Orm\\DataSource\\Summary\\SummaryMapper',
            'foreignTableName' => 'summaries',
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
