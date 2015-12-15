<?php
namespace Atlas\Orm\Mapper;

class FakeRelations extends MapperRelations
{
    protected function getNativeMapperClass()
    {
        return 'Atlas\Orm\DataSource\EmployeeMapper';
    }
}
