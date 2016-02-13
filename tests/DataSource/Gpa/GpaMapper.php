<?php
namespace Atlas\Orm\DataSource\Gpa;

use Atlas\Orm\DataSource\Student\StudentMapper;
use Atlas\Orm\Mapper\AbstractMapper;

/**
 * @inheritdoc
 */
class GpaMapper extends AbstractMapper
{
    /**
     * @inheritdoc
     */
    protected function setRelated()
    {
        $this->oneToOne('student', StudentMapper::CLASS);
    }
}
