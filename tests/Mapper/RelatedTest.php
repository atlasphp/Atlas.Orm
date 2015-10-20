<?php
namespace Atlas\Mapper;

use Atlas\Exception;
use Atlas\Table\FakeRow;
use Atlas\Table\RowIdentity;

class RelatedTest extends \PHPUnit_Framework_TestCase
{
    protected $related;

    protected function setUp()
    {
        $this->related = new Related([
            'zim' => 'gir',
            'irk' => 'doom',
        ]);
    }

    public function test__get()
    {
        // related
        $this->assertSame('gir', $this->related->zim);

        // missing
        $this->setExpectedException(
            'Atlas\Exception',
            'Atlas\Mapper\Related::$noSuchForeign does not exist'
        );
        $this->related->noSuchForeign;
    }

    public function test__set()
    {
        // related
        $this->related->zim = 'girgir';
        $this->assertSame('girgir', $this->related->zim);

        // missing
        $this->setExpectedException(
            'Atlas\Exception',
            'Atlas\Mapper\Related::$noSuchForeign does not exist'
        );
        $this->related->noSuchForeign = 'missing';
    }

    public function test__isset()
    {
        // related
        $this->assertTrue(isset($this->related->zim));

        // missing
        $this->setExpectedException(
            'Atlas\Exception',
            'Atlas\Mapper\Related::$noSuchForeign does not exist'
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
            'Atlas\Exception',
            'Atlas\Mapper\Related::$noSuchForeign does not exist'
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
