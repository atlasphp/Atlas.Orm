<?php
namespace Atlas\Orm\Table;

use Atlas\Orm\DataSource\Employee\EmployeeTable;

class IdentityMapTest extends \PHPUnit\Framework\TestCase
{
    protected $identityMap;

    protected function setUp()
    {
        $this->identityMap = new IdentityMap();
    }

    public function testSetRow()
    {
        $row = new Row(['id' => '1']);
        $this->identityMap->setRow($row, ['id' => '1'], ['id']);
        $this->expectException('Atlas\Orm\Exception');
        $this->identityMap->setRow($row, ['id' => '1'], ['id']);
    }

    public function testResetInitial_missingRow()
    {
        $row = new Row(['id' => '1']);
        $this->expectException('Atlas\Orm\Exception');
        $this->identityMap->resetInitial($row);
    }

    public function testGetInitial_missingRow()
    {
        $row = new Row(['id' => '1']);
        $this->expectException('Atlas\Orm\Exception');
        $this->identityMap->getInitial($row);
    }
}
