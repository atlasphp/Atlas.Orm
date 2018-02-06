<?php
namespace Atlas\Orm\Mapper;

use Atlas\Orm\Table\Row;
use Atlas\Orm\Table\Primary;
use StdClass;

class RecordSetTest extends \PHPUnit\Framework\TestCase
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
            'zim' => $this->getMockBuilder(RecordInterface::CLASS)->getMock(),
            'irk' => $this->getMockBuilder(RecordSetInterface::CLASS)->getMock(),
        ]);

        $this->record = new Record('FakeMapper', $this->row, $this->related);

        $newRecord = function ($cols = []) {
            $row = new Row($cols);
            $related = new Related(['zim' => null, 'irk' => null]);
            return new Record('FakeMapper', $row, $related);
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
        $this->expectException('Atlas\Orm\Exception');
        $this->recordSet[] = 'Foo';
    }

    public function testOffsetSet_nonRecordObject()
    {
        $this->expectException('Atlas\Orm\Exception');
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

    public function testAppendNew()
    {
        $record = $this->recordSet->appendNew([
            'id' => null,
            'foo' => 'newfoo'
        ]);

        $this->assertCount(2, $this->recordSet);
        $this->assertSame($record, $this->recordSet[1]);
    }

    public function testGetAndRemove()
    {
        $this->recordSet->appendNew(['id' => 2, 'foo' => 'bar1']);
        $this->recordSet->appendNew(['id' => 3, 'foo' => 'bar2']);
        $this->recordSet->appendNew(['id' => 4, 'foo' => 'bar3']);
        $this->recordSet->appendNew(['id' => 5, 'foo' => 'bar1']);
        $this->recordSet->appendNew(['id' => 6, 'foo' => 'bar2']);
        $this->recordSet->appendNew(['id' => 7, 'foo' => 'bar3']);
        $this->recordSet->appendNew(['id' => 8, 'foo' => 'bar1']);
        $this->recordSet->appendNew(['id' => 9, 'foo' => 'bar2']);
        $this->recordSet->appendNew(['id' => 10, 'foo' => 'bar3']);

        $actual = $this->recordSet->getOneBy(['foo' => 'no-such-value']);
        $this->assertNull($actual);

        $actual = $this->recordSet->getOneBy(['foo' => 'bar1']);
        $this->assertSame(2, $actual->id);

        $actual = $this->recordSet->getAllBy(['foo' => 'bar2']);
        $this->assertCount(3, $actual);
        $this->assertSame(3, $actual[2]->id);
        $this->assertSame(6, $actual[5]->id);
        $this->assertSame(9, $actual[8]->id);

        $this->assertCount(10, $this->recordSet);

        $actual = $this->recordSet->removeOneBy(['foo' => 'no-such-value']);
        $this->assertNull($actual);

        $actual = $this->recordSet->removeOneBy(['foo' => 'bar1']);
        $this->assertSame(2, $actual->id);
        $this->assertCount(9, $this->recordSet);
        $this->assertFalse(isset($this->recordSet[1]));

        $actual = $this->recordSet->removeAllBy(['foo' => 'bar2']);
        $this->assertCount(6, $this->recordSet);
        $this->assertSame(3, $actual[2]->id);
        $this->assertSame(6, $actual[5]->id);
        $this->assertSame(9, $actual[8]->id);

        $actual = $this->recordSet->removeAll();
        $this->assertCount(0, $this->recordSet);
        $this->assertCount(6, $actual);
    }

    public function testJsonSerialize()
    {
        $this->recordSet->appendNew(['id' => 2, 'foo' => 'bar1']);
        $this->recordSet->appendNew(['id' => 3, 'foo' => 'bar2']);
        $expect = '['
            . '{"id":"1","foo":"bar","baz":"dib","zim":[],"irk":[]},'
            . '{"id":2,"foo":"bar1","zim":null,"irk":null},'
            . '{"id":3,"foo":"bar2","zim":null,"irk":null}'
            . ']';
        $actual = json_encode($this->recordSet);
        $this->assertSame($expect, $actual);
    }

    public function testMarkForDeletion()
    {
        foreach ($this->recordSet as $record) {
            $this->assertFalse($record->getPersistMethod() == 'delete');
        }

        $this->recordSet->markForDeletion();

        foreach ($this->recordSet as $record) {
            $this->assertTrue($record->getPersistMethod() == 'delete');
        }
    }
}
