<?php
namespace Atlas\Orm;

class AtlasContainerTest extends \PHPUnit\Framework\TestCase
{
    protected $container;

    protected function setUp()
    {
        $this->container = new AtlasContainer('sqlite::memory:');
    }

    public function testNewAtlas()
    {
        $atlas = $this->container->newAtlas();
        $this->assertInstanceOf(Atlas::CLASS, $atlas);
    }
}
