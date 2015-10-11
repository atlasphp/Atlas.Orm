<?php
namespace Atlas\Table;

class RowIdentityTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructWithoutPrimary()
    {
        $row = new RowIdentity(['id' => null]);
        $this->assertNull($row->id);
    }

    public function testGetMissingCol()
    {
        $row = new RowIdentity(['id' => null]);
        $this->setExpectedException('Atlas\Exception');
        $row->no_such_col;
    }

    public function testSetMissingCol()
    {
        $row = new RowIdentity(['id' => null]);
        $this->setExpectedException('Atlas\Exception');
        $row->no_such_col = 'foo';
    }

    public function testSetImmutable()
    {
        $row = new RowIdentity(['id' => null]);
        $row->id = '1';

        $this->setExpectedException('Atlas\Exception');
        $row->id = '2';
    }

    public function testIsset()
    {
        $row = new RowIdentity(['id' => null]);
        $this->assertFalse(isset($row->id));
        $row->id = 1;
        $this->assertTrue(isset($row->id));
    }

    public function testUnset()
    {
        $row = new RowIdentity(['id' => null]);
        $this->assertNull($row->id);
        unset($row->id);
        $this->assertNull($row->id);
    }

    public function testUnsetMissingCol()
    {
        $row = new RowIdentity(['id' => null]);
        $this->setExpectedException('Atlas\Exception');
        unset($row->no_such_col);
    }

    public function testUnsetImmutable()
    {
        $row = new RowIdentity(['id' => '1']);
        $this->setExpectedException('Atlas\Exception');
        unset($row->id);
    }
}
