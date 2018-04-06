<?php
namespace Atlas\Orm;

class AtlasTest extends \PHPUnit\Framework\TestCase
{
    public function testNew()
    {
        $atlas = Atlas::new('sqlite::memory:');
        $this->assertInstanceOf(Atlas::CLASS, $atlas);
    }
}
