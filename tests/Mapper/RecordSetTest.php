<?php
namespace Atlas\Orm\Mapper;

use Atlas\Orm\Table\Row;
use Atlas\Orm\Table\Primary;
use StdClass;

class RecordSetTest extends \PHPUnit_Framework_TestCase
{
    protected $row;
    protected $related;
    protected $record;
    protected $recordSet;

    protected function setUp()
    {
        $this->row = new Row([
            'id' => '1',
            'foo' => 'bar',
            'baz' => 'dib',
        ]);

        $this->related = new Related([
            'zim' => 'gir',
            'irk' => 'doom',
        ]);

        $this->record = new Record('FakeMapper', $this->row, $this->related);

        $newRecord = function ($fields = []) {
            // nothing for now;
        };

        $this->recordSet = new RecordSet([$this->record], $newRecord);
    }

    public function testOffsetExists()
    {
        $this->assertTrue(isset($this->recordSet[0]));
        $this->assertFalse(isset($this->recordSet[1]));
    }

    public function testOffsetSet_append()
    {
        $this->assertCount(1, $this->recordSet);
        $this->recordSet[] = clone($this->record);
        $this->assertCount(2, $this->recordSet);
    }

    public function testOffsetSet_nonObject()
    {
        $this->setExpectedException('Atlas\Orm\Exception');
        $this->recordSet[] = 'Foo';
    }

    public function testOffsetSet_nonRecordObject()
    {
        $this->setExpectedException('Atlas\Orm\Exception');
        $this->recordSet[] = new StdClass();
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
