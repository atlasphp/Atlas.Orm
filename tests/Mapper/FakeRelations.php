<?php
namespace Atlas\Mapper;

class FakeRelations extends AbstractRelations
{
    protected function getNativeMapperClass()
    {
        return 'Atlas\DataSource\EmployeeMapper';
    }

    protected function setRelations()
    {
    }
}
