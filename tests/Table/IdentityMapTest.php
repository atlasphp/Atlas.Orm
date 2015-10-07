<?php
namespace Atlas\Table;

class IdentityMapTest extends \PHPUnit_Framework_TestCase
{
    protected $identityMap;

    protected function setUp()
    {
        $this->identityMap = new IdentityMap();
    }

    public function testSet()
    {
        $row = new Row(new RowIdentity(['id' => '1']), []);
        $this->identityMap->set($row);
        $this->setExpectedException('Atlas\Exception');
        $this->identityMap->set($row);
    }

    public function testGetPrimaryVal()
    {
        $row = new Row(new RowIdentity(['id' => '1']), []);
        $this->assertFalse($this->identityMap->getPrimaryVal($row));
        $this->identityMap->set($row);
        $this->assertSame('1', $this->identityMap->getPrimaryVal($row));
    }
}
