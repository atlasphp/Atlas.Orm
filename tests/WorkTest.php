<?php
namespace Atlas;

class WorkTest extends \PHPUnit_Framework_TestCase
{
    public function test__invoke_reInvoke()
    {
        $work = new Work('fake', function () {}, []);
        $work();
        $this->setExpectedException(Exception::CLASS, 'Cannot re-invoke prior work.');
        $work();
    }
}
