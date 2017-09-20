<?php
namespace Atlas\Orm;

use Atlas\Orm\Mapper\Record;
use Atlas\Orm\Mapper\RecordInterface;
use Atlas\Orm\Mapper\RecordSetInterface;
use Atlas\Orm\Mapper\Related;
use Atlas\Orm\Table\Primary;
use Atlas\Orm\Table\Row;

class WorkTest extends \PHPUnit\Framework\TestCase
{
    public function test__invoke_reInvoke()
    {
        $row = new Row([
            'id' => '1',
            'foo' => 'bar',
            'baz' => 'dib',
        ]);

        $related = new Related([
            'zim' => $this->getMockBuilder(RecordInterface::CLASS)->getMock(),
            'irk' => $this->getMockBuilder(RecordSetInterface::CLASS)->getMock(),
        ]);

        $record = new Record('FakeMapper', $row, $related);

        $work = new Work('fake', function () {}, $record);
        $work();
        $this->expectException(Exception::CLASS, 'Cannot re-invoke prior work.');
        $work();
    }
}
