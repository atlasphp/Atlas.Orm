<?php
namespace Atlas\Orm;

class ContainerTest extends \PHPUnit\Framework\TestCase
{
    protected $container;

    protected function setUp()
    {
        $this->container = new Container('sqlite::memory:');
    }

    public function testNewAtlas()
    {
        $atlas = $this->container->newAtlas();
        $this->assertInstanceOf(Atlas::CLASS, $atlas);
    }
}
