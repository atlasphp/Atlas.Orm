<?php
namespace Atlas\Orm\Mapper;

class FakeRelations extends AbstractRelations
{
    protected function getNativeMapperClass()
    {
        return 'Atlas\Orm\DataSource\EmployeeMapper';
    }

    protected function setRelations()
    {
    }
}
