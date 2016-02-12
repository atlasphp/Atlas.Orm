<?php
namespace Atlas\Orm\DataSource\Enrollment;

use Atlas\Orm\DataSource\Course\CourseMapper;
use Atlas\Orm\DataSource\Student\StudentMapper;
use Atlas\Orm\Mapper\AbstractMapper;

/**
 * @inheritdoc
 */
class EnrollmentMapper extends AbstractMapper
{
    /**
     * @inheritdoc
     */
    protected function setRelated()
    {
        $this->manyToOne('course', CourseMapper::CLASS);
        $this->manyToOne('student', StudentMapper::CLASS);
    }
}
