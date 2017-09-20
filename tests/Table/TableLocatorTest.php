<?php
namespace Atlas\Orm\Table;

use StdClass;

class TableLocatorTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp()
    {
        $this->tableLocator = new TableLocator();
        $this->tableLocator->set('Foo', function () {
            return new StdClass();
        });
    }

    public function testHas()
    {
        $this->assertTrue($this->tableLocator->has('Foo'));
        $this->assertFalse($this->tableLocator->has('Bar'));
    }

    public function testGet()
    {
        $actual = $this->tableLocator->get('Foo');
        $this->assertInstanceOf('StdClass', $actual);

        $again = $this->tableLocator->get('Foo');
        $this->assertSame($actual, $again);

        $this->expectException(
            'Atlas\Orm\Exception',
            'Bar not found in table locator.'
        );
        $this->tableLocator->get('Bar');
    }
}
