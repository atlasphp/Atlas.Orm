<?php
namespace Atlas\Orm;

use Atlas\Pdo\ConnectionLocator;
use Atlas\Orm\Transaction\BeginOnRead;
use ReflectionClass;

class AtlasBuilderTest extends \PHPUnit\Framework\TestCase
{
    protected $builder;

    protected function setUp()
    {
        $this->builder = new AtlasBuilder('sqlite::memory:');
    }

    public function testGetConnectionLocator()
    {
        $this->assertInstanceOf(
            ConnectionLocator::CLASS,
            $this->builder->getConnectionLocator()
        );
    }

    public function testSetTransactionClass()
    {
        $this->builder->setTransactionClass(BeginOnRead::CLASS);
        $atlas = $this->builder->newAtlas();
        $actual = $this->getProperty($atlas, 'transaction');
        $this->assertInstanceOf(BeginOnRead::CLASS, $actual);
    }

    public function testSetFactory()
    {
        $factory = function (string $class) { };
        $this->builder->setFactory($factory);
        $actual = $this->getProperty($this->builder, 'factory');
        $this->assertSame($factory, $actual);
    }

    protected function getProperty($object, $name)
    {
        $rclass = new ReflectionClass(get_class($object));
        $rprop = $rclass->getProperty($name);
        $rprop->setAccessible(true);
        return $rprop->getValue($object);
    }
}
