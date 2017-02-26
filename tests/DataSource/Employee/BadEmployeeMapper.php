<?php
namespace Atlas\Orm\DataSource\Employee;

use Atlas\Orm\Mapper\AbstractMapper;

class BadEmployeeMapper extends AbstractMapper
{
    protected function setRelated()
    {
        // cannot use row name as related name
        $this->oneToOne('name', BadEmployee::CLASS);
    }
}
