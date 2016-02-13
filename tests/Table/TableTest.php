<?php
namespace Atlas\Orm\Table;

use Atlas\Orm\DataSource\Employee\EmployeeTable;
use Atlas\Orm\DataSource\Student\StudentTable;

class TableTest extends \PHPUnit_Framework_TestCase
{
    public function testGetRowClass()
    {
        // generic row
        $table = new StudentTable();
        $actual = $table->getRowClass();
        $expect = 'Atlas\Orm\Table\Row';
        $this->assertSame($expect, $actual);

        // custom row
        $table = new EmployeeTable();
        $actual = $table->getRowClass();
        $expect = 'Atlas\Orm\DataSource\Employee\EmployeeRow';
        $this->assertSame($expect, $actual);
    }

    public function testCalcPrimary()
    {
        $table = new EmployeeTable();

        // plain old primary value
        $actual = $table->calcPrimary(1);
        $expect = ['id' => 1];
        $this->assertSame($expect, $actual);

        // primary embedded in array
        $actual = $table->calcPrimary([
            'id' => 2,
            'foo' => 'bar',
            'baz' => 'dib'
        ]);
        $expect = ['id' => 2];

        // not a scalar
        $this->setExpectedException(
            'Atlas\Orm\Exception',
            'Primary key values must be scalar.'
        );
        $table->calcPrimary([1, 2, 3]);
    }

    public function testCalcPrimaryComposite_notArray()
    {
        $table = new StudentTable();
        $this->setExpectedException(
            'Atlas\Orm\Exception',
            'Composite primary keys must be associative arrays.'
        );
        $table->calcPrimary(1);
    }

    public function testCalcPrimaryComposite_missingKey()
    {
        $table = new StudentTable();
        $this->setExpectedException(
            'Atlas\Orm\Exception',
            "Primary key value for 'student_ln' is missing"
        );
        $table->calcPrimary(['student_fn' => 'Anna']);
    }

    public function testCalcPrimaryComposite_nonScalar()
    {
        $table = new StudentTable();
        $this->setExpectedException(
            'Atlas\Orm\Exception',
            "Primary key value for 'student_fn' must be scalar"
        );
        $table->calcPrimary(['student_fn' => ['Anna', 'Betty', 'Clara']]);
    }

    public function testCalcPrimaryComposite()
    {
        $table = new StudentTable();
        $actual = $table->calcPrimary([
            'foo' => 'bar',
            'student_fn' => 'Anna',
            'student_ln' => 'Alpha',
            'baz' => 'dib',
        ]);
        $expect = [
            'student_fn' => 'Anna',
            'student_ln' => 'Alpha',
        ];
        $this->assertSame($expect, $actual);
    }
}
