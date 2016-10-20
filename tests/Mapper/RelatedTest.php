<?php
namespace Atlas\Orm\Mapper;

use Atlas\Orm\Exception;
use Atlas\Orm\TestCase;

class RelatedTest extends TestCase
{
    protected $zim;
    protected $irk;
    protected $related;

    protected function setUp()
    {
        $this->zim = $this->getMockFromBuilder(RecordInterface::CLASS);
        $this->irk = $this->getMockFromBuilder(RecordSetInterface::CLASS);
        $this->related = new Related([
            'zim' => $this->zim,
            'irk' => $this->irk,
        ]);
    }

    public function test__get()
    {
        // related
        $this->assertSame($this->zim, $this->related->zim);

        // missing
        $this->setExpectedException(
            'Atlas\Orm\Exception',
            'Atlas\Orm\Mapper\Related::$noSuchForeign does not exist'
        );
        $this->related->noSuchForeign;
    }

    public function test__set()
    {
        $newZim = $this->getMockFromBuilder(RecordInterface::CLASS);

        // related
        $this->related->zim = $newZim;
        $this->assertSame($newZim, $this->related->zim);

        // missing
        $this->setExpectedException(
            'Atlas\Orm\Exception',
            'Atlas\Orm\Mapper\Related::$noSuchForeign does not exist'
        );
        $this->related->noSuchForeign = 'missing';
    }

    public function test__isset()
    {
        // related
        $this->assertTrue(isset($this->related->zim));

        // missing
        $this->setExpectedException(
            'Atlas\Orm\Exception',
            'Atlas\Orm\Mapper\Related::$noSuchForeign does not exist'
        );
        isset($this->related->noSuchForeign);
    }

    public function test__unset()
    {
        // related
        unset($this->related->zim);
        $this->assertNull($this->related->zim);

        // missing
        $this->setExpectedException(
            'Atlas\Orm\Exception',
            'Atlas\Orm\Mapper\Related::$noSuchForeign does not exist'
        );
        unset($this->related->noSuchForeign);
    }

    public function testHas()
    {
        // related
        $this->assertTrue($this->related->has('zim'));

        // missing
        $this->assertFalse($this->related->has('noSuchForeign'));
    }
}
