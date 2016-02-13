<?php
namespace Atlas\Orm\Table;

use StdClass;

class GatewayLocatorTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->gatewayLocator = new GatewayLocator();
        $this->gatewayLocator->set('Foo', function () {
            return new StdClass();
        });
    }

    public function testHas()
    {
        $this->assertTrue($this->gatewayLocator->has('Foo'));
        $this->assertFalse($this->gatewayLocator->has('Bar'));
    }

    public function testGet()
    {
        $actual = $this->gatewayLocator->get('Foo');
        $this->assertInstanceOf('StdClass', $actual);

        $again = $this->gatewayLocator->get('Foo');
        $this->assertSame($actual, $again);

        $this->setExpectedException(
            'Atlas\Orm\Exception',
            'Bar not found in gateway locator.'
        );
        $this->gatewayLocator->get('Bar');
    }
}
