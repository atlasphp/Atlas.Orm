<?php
namespace Atlas\Orm\DataSource\Student;

use Atlas\Orm\DataSource\Course\CourseMapper;
use Atlas\Orm\DataSource\Degree\DegreeMapper;
use Atlas\Orm\DataSource\Gpa\GpaMapper;
use Atlas\Orm\DataSource\Enrollment\EnrollmentMapper;
use Atlas\Orm\Mapper\AbstractMapper;

/**
 * @inheritdoc
 */
class StudentMapper extends AbstractMapper
{
    /**
     * @inheritdoc
     */
    protected function setRelated()
    {
        $this->oneToOne('gpa', GpaMapper::CLASS);
        $this->manyToOne('degree', DegreeMapper::CLASS)->ignoreCase();
        $this->oneToMany('enrollments', EnrollmentMapper::CLASS);
        $this->manyToMany('courses', CourseMapper::CLASS, 'enrollments');

        $this->oneToMany('engl_enrollments', EnrollmentMapper::CLASS)
            ->where('course_subject = ?', 'ENGL');
    }
}
