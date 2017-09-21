<?php
namespace Atlas\Orm\Relationship;

class RelationshipTest extends \PHPUnit\Framework\TestCase
{
    public function testValuesMatch()
    {
        $fake = new FakeRelationship();
        $this->assertFalse($fake->valuesMatch('1', 'a'));
    }
}
