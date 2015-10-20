<?php
namespace Atlas\Mapper;

use Atlas\Table\FakeRow;
use Atlas\Table\FakeRowIdentity;
use InvalidArgumentException;

class RecordSetTest extends \PHPUnit_Framework_TestCase
{
    protected $row;
    protected $related;
    protected $record;
    protected $recordSet;

    protected function setUp()
    {
        $this->row = new FakeRow(
            new FakeRowIdentity(['id' => '1']),
            [
                'foo' => 'bar',
                'baz' => 'dib',
            ]
        );

        $this->related = new Related([
            'zim' => 'gir',
            'irk' => 'doom',
        ]);

        $this->record = new FakeRecord($this->row, $this->related);

        $this->recordSet = new RecordSet([], FakeRecord::CLASS);
        $this->recordSet[] = $this->record;
    }

    public function testOffsetExists()
    {
        $this->assertTrue(isset($this->recordSet[0]));
        $this->assertFalse(isset($this->recordSet[1]));
    }

    public function testOffsetSet_nonObject()
    {
        $this->setExpectedException(InvalidArgumentException::CLASS);
        $this->recordSet[] = Record::CLASS;
    }

    public function testOffsetUnset()
    {
        $this->assertFalse($this->recordSet->isEmpty());
        $this->assertTrue(isset($this->recordSet[0]));
        unset($this->recordSet[0]);
        $this->assertFalse(isset($this->recordSet[0]));
        $this->assertTrue($this->recordSet->isEmpty());
    }
}
