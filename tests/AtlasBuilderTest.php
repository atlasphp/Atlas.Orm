<?php
namespace Atlas\Orm;

class AtlasBuilderTest extends \PHPUnit\Framework\TestCase
{
    protected $builder;

    protected function setUp()
    {
        $this->builder = new AtlasBuilder('sqlite::memory:');
    }

    public function testNewAtlas()
    {
        $atlas = $this->builder->newAtlas();
        $this->assertInstanceOf(Atlas::CLASS, $atlas);
    }
}
