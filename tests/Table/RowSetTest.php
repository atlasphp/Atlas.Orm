<?php
namespace Atlas\Orm\Table;

use InvalidArgumentException;

class RowSetTest extends \PHPUnit_Framework_TestCase
{
    protected $row;
    protected $rowSet;

    protected function setUp()
    {
        $this->row = new FakeRow(
            new FakeRowIdentity(['id' => '1']),
            [
                'foo' => 'bar',
                'baz' => 'dib',
            ]
        );

        $this->rowSet = new FakeRowSet(new FakeRowFactory());
        $this->rowSet[] = $this->row;
    }

    public function testOffsetExists()
    {
        $this->assertTrue(isset($this->rowSet[0]));
        $this->assertFalse(isset($this->rowSet[1]));
    }

    public function testOffsetSet_nonObject()
    {
        $this->setExpectedException(InvalidArgumentException::CLASS);
        $this->rowSet[] = FakeRow::CLASS;
    }

    public function testOffsetUnset()
    {
        $this->assertTrue(isset($this->rowSet[0]));
        unset($this->rowSet[0]);
        $this->assertFalse(isset($this->rowSet[0]));
    }

    public function testGetArrayCopy()
    {
        $expect = [
            0 => $this->row->getArrayCopy(),
        ];
        $actual = $this->rowSet->getArrayCopy();
        $this->assertSame($expect, $actual);
    }
}
