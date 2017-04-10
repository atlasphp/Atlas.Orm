<?php
namespace Atlas\Orm\DataSource\Degree;

use Atlas\Orm\DataSource\Student\StudentMapper;
use Atlas\Orm\Mapper\AbstractMapper;

/**
 * @inheritdoc
 */
class DegreeMapper extends AbstractMapper
{
    /**
     * @inheritdoc
     */
    protected function setRelated()
    {
        $this->oneToMany('students', StudentMapper::CLASS)->ignoreCase();
    }
}
