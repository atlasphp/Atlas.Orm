<?php
namespace Atlas\Table;

class IdentityMapTest extends \PHPUnit_Framework_TestCase
{
    protected $identityMap;

    protected function setUp()
    {
        $this->identityMap = new IdentityMap();
    }

    public function testSetRow()
    {
        $row = new FakeRow(new FakeRowIdentity(['id' => '1']), []);
        $this->identityMap->setRow($row, ['id' => '1']);
        $this->setExpectedException('Atlas\Exception');
        $this->identityMap->setRow($row, ['id' => '1']);
    }

    public function testSetInitial_missingRow()
    {
        $row = new FakeRow(new FakeRowIdentity(['id' => '1']), []);
        $this->setExpectedException('Atlas\Exception');
        $this->identityMap->setInitial($row);
    }

    public function testGetInitial_missingRow()
    {
        $row = new FakeRow(new FakeRowIdentity(['id' => '1']), []);
        $this->setExpectedException('Atlas\Exception');
        $this->identityMap->getInitial($row);
    }
}
