<?php
namespace Atlas\Orm\Table;

class RowTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructWithoutPrimary()
    {
        $row = new Row(new Primary(['id' => null]), []);
        $this->assertNull($row->id);
    }

    public function testGetMissingCol()
    {
        $row = new Row(new Primary(['id' => null]), []);
        $this->setExpectedException('Atlas\Orm\Exception');
        $row->no_such_col;
    }

    public function testSetMissingCol()
    {
        $row = new Row(new Primary(['id' => null]), []);
        $this->setExpectedException('Atlas\Orm\Exception');
        $row->no_such_col = 'foo';
    }

    public function testSetImmutable()
    {
        $row = new Row(new Primary(['id' => null]), []);
        $row->id = '1';

        $this->setExpectedException('Atlas\Orm\Exception');
        $row->id = '2';
    }

    public function testIsset()
    {
        $row = new Row(new Primary(['id' => null]), []);
        $this->assertFalse(isset($row->id));
        $row->id = 1;
        $this->assertTrue(isset($row->id));
    }

    public function testUnset()
    {
        $row = new Row(new Primary(['id' => null]), ['foo' => 'bar']);
        $this->assertSame('bar', $row->foo);
        unset($row->foo);
        $this->assertNull($row->foo);
    }

    public function testUnsetIdentity()
    {
        $row = new Row(new Primary(['id' => null]), ['foo' => 'bar']);
        $this->assertNull($row->id);
        unset($row->id);
        $this->assertNull($row->id);
    }

    public function testUnsetMissingCol()
    {
        $row = new Row(new Primary(['id' => null]), []);
        $this->setExpectedException('Atlas\Orm\Exception');
        unset($row->no_such_col);
    }

    public function testUnsetImmutable()
    {
        $row = new Row(new Primary(['id' => '1']), []);
        $this->setExpectedException('Atlas\Orm\Exception');
        unset($row->id);
    }
}
