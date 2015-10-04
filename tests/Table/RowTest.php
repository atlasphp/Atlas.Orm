<?php
namespace Atlas\Table;

class RowTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructWithoutPrimary()
    {
        $row = new Row([], 'id');
        $this->assertNull($row->id);
    }

    public function testGetMissingCol()
    {
        $row = new Row([], 'id');
        $this->setExpectedException('Atlas\Exception');
        $row->no_such_col;
    }

    public function testSetMissingCol()
    {
        $row = new Row([], 'id');
        $this->setExpectedException('Atlas\Exception');
        $row->no_such_col = 'foo';
    }

    public function testSetImmutable()
    {
        $row = new Row([], 'id');
        $row->id = '1';

        $this->setExpectedException('Atlas\Exception');
        $row->id = '2';
    }

    public function testIsset()
    {
        $row = new Row([], 'id');
        $this->assertFalse(isset($row->id));
        $row->id = 1;
        $this->assertTrue(isset($row->id));
    }

    public function testUnset()
    {
        $row = new Row(['foo' => 'bar'], 'id');
        $this->assertSame('bar', $row->foo);
        unset($row->foo);
        $this->assertNull($row->foo);
    }

    public function testUnsetMissingCol()
    {
        $row = new Row([], 'id');
        $this->setExpectedException('Atlas\Exception');
        unset($row->no_such_col);
    }

    public function testUnsetImmutable()
    {
        $row = new Row(['id' => '1'], 'id');
        $this->setExpectedException('Atlas\Exception');
        unset($row->id);
    }

    public function testGetPrimaryCol()
    {
        $row = new Row(['id' => '1'], 'id');
        $this->assertSame('id', $row->getPrimaryCol());
    }

    public function testGetObjectCopy()
    {
        $row = new Row(['id' => '1', 'foo' => 'bar'], 'id');
        $expect = (object) ['id' => '1', 'foo' => 'bar'];
        $this->assertEquals($expect, $row->getObjectCopy());
    }
}
