<?php
namespace Atlas\Orm\Mapper;

use Atlas\Orm\Exception;
use Atlas\Orm\Table\Row;
use Atlas\Orm\Table\Primary;

class RecordTest extends \PHPUnit\Framework\TestCase
{
    protected $row;
    protected $related;
    protected $record;
    protected $zim;
    protected $irk;

    protected function setUp()
    {
        $this->zim = $this->getMockBuilder(RecordInterface::CLASS)->getMock();
        $this->irk = $this->getMockBuilder(RecordSetInterface::CLASS)->getMock();

        $this->row = new Row([
            'id' => '1',
            'foo' => 'bar',
            'baz' => 'dib',
        ]);

        $this->related = new Related([
            'zim' => $this->zim,
            'irk' => $this->irk,
        ]);

        $this->record = new Record('FakeMapper', $this->row, $this->related);
    }

    public function testGetRow()
    {
        $this->assertSame($this->row, $this->record->getRow());
    }

    public function testGetRelated()
    {
        $this->assertSame($this->related, $this->record->getRelated());
    }

    public function test__get()
    {
        // row
        $this->assertSame('bar', $this->record->foo);

        // related
        $this->assertSame($this->zim, $this->record->zim);

        // missing
        $this->expectException(
            'Atlas\Orm\Exception',
            'Atlas\Orm\Mapper\Record::$noSuchField does not exist'
        );
        $this->record->noSuchField;
    }

    public function test__set()
    {
        // row
        $this->record->foo = 'barbar';
        $this->assertSame('barbar', $this->record->foo);
        $this->assertSame('barbar', $this->row->foo);

        // related
        $newZim = $this->getMockBuilder(RecordInterface::CLASS)->getMock();
        $this->record->zim = $newZim;
        $this->assertSame($newZim, $this->record->zim);

        // missing
        $this->expectException(
            'Atlas\Orm\Exception',
            'Atlas\Orm\Mapper\Record::$noSuchField does not exist'
        );
        $this->record->noSuchField = 'missing';
    }

    public function test__isset()
    {
        // row
        $this->assertTrue(isset($this->record->foo));

        // related
        $this->assertTrue(isset($this->record->zim));

        // missing
        $this->expectException(
            'Atlas\Orm\Exception',
            'Atlas\Orm\Mapper\Record::$noSuchField does not exist'
        );
        isset($this->record->noSuchField);
    }

    public function test__unset()
    {
        // row
        unset($this->record->foo);
        $this->assertNull($this->record->foo);
        $this->assertNull($this->row->foo);

        // related
        unset($this->record->zim);
        $this->assertNull($this->record->zim);

        // missing
        $this->expectException(
            'Atlas\Orm\Exception',
            'Atlas\Orm\Mapper\Record::$noSuchField does not exist'
        );
        unset($this->record->noSuchField);
    }

    public function testHas()
    {
        // row
        $this->assertTrue($this->record->has('foo'));

        // related
        $this->assertTrue($this->record->has('zim'));

        // missing
        $this->assertFalse($this->record->has('noSuchField'));
    }

    public function testSet()
    {
        $newZim = $this->getMockBuilder(RecordInterface::CLASS)->getMock();
        $newZim->method('getArrayCopy')->willReturn(['zimkey' => 'zimval']);

        $newIrk = $this->getMockBuilder(RecordSetInterface::CLASS)->getMock();
        $newIrk->method('getArrayCopy')->willReturn([['doomkey' => 'doomval']]);

        $this->record->set([
            'foo' => 'hello',
            'zim' => $newZim,
            'irk' => $newIrk,
        ]);

        $actual = $this->record->getArrayCopy();
        $expected = [
            'id' => '1',
            'foo' => 'hello',
            'baz' => 'dib',
            'zim' => ['zimkey' => 'zimval'],
            'irk' => [
                ['doomkey' => 'doomval']
            ],
        ];
        $this->assertSame($expected, $actual);
    }

    public function testJsonSerialize()
    {
        $actual = json_encode($this->record);
        $expect = '{"id":"1","foo":"bar","baz":"dib","zim":[],"irk":[]}';
        $this->assertSame($expect, $actual);
    }

    public function testGetPersistMethod()
    {
        // starts as new
        $this->assertSame('insert', $this->record->getPersistMethod());

        $this->record->markForDeletion();
        $this->assertSame('delete', $this->record->getPersistMethod());

        // should go back to new
        $this->record->markForDeletion(false);
        $this->assertSame('insert', $this->record->getPersistMethod());

        $this->record->getRow()->setStatus(Row::MODIFIED);
        $this->assertSame('update', $this->record->getPersistMethod());

        $this->record->getRow()->setStatus(Row::FOR_DELETE);
        $this->assertSame('delete', $this->record->getPersistMethod());

        $this->record->getRow()->setStatus(Row::SELECTED);
        $this->assertNull($this->record->getPersistMethod());
    }
}
