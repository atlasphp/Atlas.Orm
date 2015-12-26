<?php
namespace Atlas\Orm\Mapper;

class FakeRelations extends Relations
{
    protected function getNativeMapperClass()
    {
        return 'Atlas\Orm\DataSource\EmployeeMapper';
    }
}
