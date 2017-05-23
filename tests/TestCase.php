<?php
namespace Atlas\Orm;

class TestCase extends \PHPUnit_Framework_TestCase
{
    protected function getMockFromBuilder($class)
    {
        $mock = $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->getMock();

        return $mock;
    }
}
