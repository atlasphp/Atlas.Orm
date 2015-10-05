<?php
namespace Atlas\Table;

class RowTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructWithoutPrimary()
    {
        $row = new Row(new RowIdentity('id', null), []);
        $this->assertNull($row->id);
    }

    public function testGetMissingCol()
    {
        $row = new Row(new RowIdentity('id', null), []);
        $this->setExpectedException('Atlas\Exception');
        $row->no_such_col;
    }

    public function testSetMissingCol()
    {
        $row = new Row(new RowIdentity('id', null), []);
        $this->setExpectedException('Atlas\Exception');
        $row->no_such_col = 'foo';
    }

    public function testSetImmutable()
    {
        $row = new Row(new RowIdentity('id', null), []);
        $row->id = '1';

        $this->setExpectedException('Atlas\Exception');
        $row->id = '2';
    }

    public function testIsset()
    {
        $row = new Row(new RowIdentity('id', null), []);
        $this->assertFalse(isset($row->id));
        $row->id = 1;
        $this->assertTrue(isset($row->id));
    }

    public function testUnset()
    {
        $row = new Row(new RowIdentity('id', null), ['foo' => 'bar']);
        $this->assertSame('bar', $row->foo);
        unset($row->foo);
        $this->assertNull($row->foo);
    }

    public function testUnsetMissingCol()
    {
        $row = new Row(new RowIdentity('id', null), []);
        $this->setExpectedException('Atlas\Exception');
        unset($row->no_such_col);
    }

    public function testUnsetImmutable()
    {
        $row = new Row(new RowIdentity('id', '1'), []);
        $this->setExpectedException('Atlas\Exception');
        unset($row->id);
    }

    public function testGetObjectCopy()
    {
        $row = new Row(new RowIdentity('id', '1'), ['foo' => 'bar']);
        $expect = (object) ['id' => '1', 'foo' => 'bar'];
        $this->assertEquals($expect, $row->getObjectCopy());
    }
}
