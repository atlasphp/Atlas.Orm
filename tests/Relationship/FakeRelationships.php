<?php
namespace Atlas\Orm\Relationship;

class FakeRelationships extends Relationships
{
    protected function getNativeMapperClass()
    {
        return 'Atlas\Orm\DataSource\EmployeeMapper';
    }
}
