<?php
namespace Atlas\Orm\Relation;

class FakeRelations extends Relations
{
    protected function getNativeMapperClass()
    {
        return 'Atlas\Orm\DataSource\EmployeeMapper';
    }
}
