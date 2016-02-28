<?php
namespace Atlas\Orm;

use Atlas\Orm\Mapper\Record;
use Atlas\Orm\Mapper\Related;
use Atlas\Orm\Table\Primary;
use Atlas\Orm\Table\Row;

class WorkTest extends \PHPUnit_Framework_TestCase
{
    public function test__invoke_reInvoke()
    {
        $row = new Row([
            'id' => '1',
            'foo' => 'bar',
            'baz' => 'dib',
        ]);

        $related = new Related([
            'zim' => 'gir',
            'irk' => 'doom',
        ]);

        $record = new Record('FakeMapper', $row, $related);

        $work = new Work('fake', function () {}, $record);
        $work();
        $this->setExpectedException(Exception::CLASS, 'Cannot re-invoke prior work.');
        $work();
    }
}
