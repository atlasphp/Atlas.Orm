<?php
namespace Atlas\Orm\DataSource\Course;

use Atlas\Orm\DataSource\Enrollment\EnrollmentMapper;
use Atlas\Orm\DataSource\Student\StudentMapper;
use Atlas\Orm\Mapper\AbstractMapper;

/**
 * @inheritdoc
 */
class CourseMapper extends AbstractMapper
{
    /**
     * @inheritdoc
     */
    protected function setRelated()
    {
        $this->oneToMany('enrollments', EnrollmentMapper::CLASS);
        $this->manyToMany('students', StudentMapper::CLASS, 'enrollments');
    }
}
