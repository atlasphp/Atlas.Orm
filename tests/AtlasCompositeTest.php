<?php
namespace Atlas\Orm;

use Atlas\Orm\DataSource\Course\CourseMapper;
use Atlas\Orm\DataSource\Degree\DegreeMapper;
use Atlas\Orm\DataSource\Enrollment\EnrollmentMapper;
use Atlas\Orm\DataSource\Gpa\GpaMapper;
use Atlas\Orm\DataSource\Student\StudentMapper;
use Aura\Sql\ExtendedPdo;

class AtlasCompositeTest extends \PHPUnit\Framework\TestCase
{
    protected $atlas;

    // The $expect* properties are at the end, because they are so long

    protected function setUp()
    {
        $atlasContainer = new AtlasContainer('sqlite::memory:');
        $atlasContainer->setMappers([
            CourseMapper::CLASS,
            DegreeMapper::CLASS,
            EnrollmentMapper::CLASS,
            GpaMapper::CLASS,
            StudentMapper::CLASS,
        ]);

        $connection = $atlasContainer->getConnectionLocator()->getDefault();
        $fixture = new SqliteFixture($connection);
        $fixture->exec();

        $this->atlas = $atlasContainer->getAtlas();
    }

    public function testFetchRecord()
    {
        $actual = $this->atlas->fetchRecord(
            StudentMapper::CLASS,
            ['student_fn' => 'Anna', 'student_ln' => 'Alpha'],
            [
                'degree',
                'gpa',
                'enrollments',
                'courses',
            ]
        )->getArrayCopy();

        $this->assertSame($this->expectRecord, $actual);
    }

    public function testFetchRecordBy()
    {
        $actual = $this->atlas->fetchRecordBy(
            StudentMapper::CLASS,
            ['student_fn' => 'Anna'],
            [
                'degree',
                'gpa',
                'enrollments',
                'courses',
            ]
        )->getArrayCopy();

        $this->assertSame($this->expectRecord, $actual);
    }

    public function testFetchRecordSet()
    {
        $actual = $this->atlas->fetchRecordSet(
            StudentMapper::CLASS,
            [
                ['student_fn' => 'Anna', 'student_ln' => 'Alpha'],
                ['student_fn' => 'Betty', 'student_ln' => 'Beta'],
                ['student_fn' => 'Clara', 'student_ln' => 'Clark'],
            ],
            [
                'degree',
                'gpa',
                'enrollments' => function ($q) { $q->orderBy(['course_subject', 'course_number']); },
                'courses' => function ($q) { $q->orderBy(['course_subject', 'course_number']); },
            ]
        )->getArrayCopy();

        foreach ($this->expectRecordSet as $i => $expect) {
            $this->assertSame($expect, $actual[$i], "record $i not the same");
        }
    }

    public function testFetchRecordSetBy()
    {
        // note that we canno to
        $actual = $this->atlas->fetchRecordSetBy(
            StudentMapper::CLASS,
            ['student_fn' => ['Anna', 'Betty', 'Clara']],
            [
                'degree',
                'gpa',
                'enrollments' => function ($q) { $q->orderBy(['course_subject', 'course_number']); },
                'courses' => function ($q) { $q->orderBy(['course_subject', 'course_number']); },
            ]
        )->getArrayCopy();

        foreach ($this->expectRecordSet as $i => $expect) {
            $this->assertSame($expect, $actual[$i], "record $i not the same");
        }
    }

    public function testSelect_fetchRecord()
    {
        $actual = $this->atlas
            ->select(StudentMapper::CLASS)
            ->where('student_fn = ?', 'Anna')
            ->with([
                'degree',
                'gpa',
                'enrollments',
                'courses',
            ])
            ->fetchRecord();

        $this->assertSame($this->expectRecord, $actual->getArrayCopy());
    }

    public function testSelect_fetchRecordSet()
    {
        $actual = $this->atlas
            ->select(StudentMapper::CLASS)
            ->where('student_fn < ?', 'D')
            ->with([
                'degree',
                'gpa',
                'enrollments' => function ($q) { $q->orderBy(['course_subject', 'course_number']); },
                'courses' => function ($q) { $q->orderBy(['course_subject', 'course_number']); },
            ])
            ->fetchRecordSet()
            ->getArrayCopy();

        foreach ($this->expectRecordSet as $i => $expect) {
            $this->assertSame($expect, $actual[$i], "record $i not the same");
        }
    }

    public function testSingleRelatedInRecordSet()
    {
        $degree = $this->atlas->fetchRecordBy(
            DegreeMapper::CLASS,
            [
                'degree_type' => 'BS',
                'degree_subject' => 'MATH',
            ]
        );
        $expect = $degree->getRow();

        $students = $this->atlas->fetchRecordSetBy(
            StudentMapper::CLASS,
            [
                'degree_type' => 'BS',
                'degree_subject' => 'MATH',
            ],
            [
                'degree',
            ]
        );

        foreach ($students as $student) {
            $actual = $student->degree->getRow();
            $this->assertSame($expect, $actual);
        }
    }

    public function testCalcPrimaryComposite_missingKey()
    {
        $this->expectException(
            'Atlas\Orm\Exception',
            "Expected scalar value for primary key 'student_ln', value is missing instead."
        );
        $this->atlas->fetchRecord(StudentMapper::CLASS, ['student_fn' => 'Anna']);
    }

    public function testCalcPrimaryComposite_nonScalar()
    {
        $this->expectException(
            'Atlas\Orm\Exception',
            "Expected scalar value for primary key 'student_fn', got array instead."
        );
        $this->atlas->fetchRecord(
            StudentMapper::CLASS,
            ['student_fn' => ['Anna', 'Betty', 'Clara']]
        );
    }

    public function testCalcPrimaryComposite()
    {
        $actual = $this->atlas->fetchRecord(
            StudentMapper::CLASS,
            [
                'foo' => 'bar',
                'student_fn' => 'Anna',
                'student_ln' => 'Alpha',
                'baz' => 'dib',
            ]
        );

        $this->assertSame('Anna', $actual->student_fn);
        $this->assertSame('Alpha', $actual->student_ln);
    }

    public function testRelationshipWhere()
    {
        $student = $this->atlas->fetchRecord(
            StudentMapper::CLASS,
            [
                'student_fn' => 'Anna',
                'student_ln' => 'Alpha',
            ],
            [
                'engl_enrollments',
            ]
        );

        $actual = $student->engl_enrollments->getArrayCopy();

        $expect = [
            [
                'student_fn' => 'Anna',
                'student_ln' => 'Alpha',
                'course_subject' => 'ENGL',
                'course_number' => '100',
                'grade' => '65',
                'points' => '1',
                'course' => NULL,
                'student' => NULL,
            ]
        ];

        $this->assertSame($expect, $actual);
    }

    protected $expectRecord = [
        'student_fn' => 'Anna',
        'student_ln' => 'Alpha',
        'degree_type' => 'BA',
        'degree_subject' => 'ENGL',
        'gpa' => [
            'student_fn' => 'Anna',
            'student_ln' => 'Alpha',
            'gpa' => '1.333',
            'student' => null,
        ],
        'degree' => [
            'degree_type' => 'ba',
            'degree_subject' => 'engl',
            'title' => 'Bachelor of Arts, English',
            'students' => null,
        ],
        'enrollments' => [
            0 => [
                'student_fn' => 'Anna',
                'student_ln' => 'Alpha',
                'course_subject' => 'ENGL',
                'course_number' => '100',
                'grade' => '65',
                'points' => '1',
                'course' => null,
                'student' => null,
            ],
            1 => [
                'student_fn' => 'Anna',
                'student_ln' => 'Alpha',
                'course_subject' => 'HIST',
                'course_number' => '100',
                'grade' => '68',
                'points' => '1',
                'course' => null,
                'student' => null,
            ],
            2 => [
                'student_fn' => 'Anna',
                'student_ln' => 'Alpha',
                'course_subject' => 'MATH',
                'course_number' => '100',
                'grade' => '71',
                'points' => '2',
                'course' => null,
                'student' => null,
            ],
        ],
        'courses' => [
            0 => [
                'course_subject' => 'ENGL',
                'course_number' => '100',
                'title' => 'Composition',
                'enrollments' => null,
                'students' => null,
            ],
            1 => [
                'course_subject' => 'HIST',
                'course_number' => '100',
                'title' => 'World History',
                'enrollments' => null,
                'students' => null,
            ],
            2 => [
                'course_subject' => 'MATH',
                'course_number' => '100',
                'title' => 'Algebra',
                'enrollments' => null,
                'students' => null,
            ],
        ],
        'engl_enrollments' => null,
    ];

    protected $expectRecordSet = [
        0 => [
            'student_fn' => 'Anna',
            'student_ln' => 'Alpha',
            'degree_type' => 'BA',
            'degree_subject' => 'ENGL',
            'gpa' => [
                'student_fn' => 'Anna',
                'student_ln' => 'Alpha',
                'gpa' => '1.333',
                'student' => NULL,
            ],
            'degree' => [
                'degree_type' => 'ba',
                'degree_subject' => 'engl',
                'title' => 'Bachelor of Arts, English',
                'students' => NULL,
            ],
            'enrollments' => [
                0 => [
                    'student_fn' => 'Anna',
                    'student_ln' => 'Alpha',
                    'course_subject' => 'ENGL',
                    'course_number' => '100',
                    'grade' => '65',
                    'points' => '1',
                    'course' => NULL,
                    'student' => NULL,
                ],
                1 => [
                    'student_fn' => 'Anna',
                    'student_ln' => 'Alpha',
                    'course_subject' => 'HIST',
                    'course_number' => '100',
                    'grade' => '68',
                    'points' => '1',
                    'course' => NULL,
                    'student' => NULL,
                ],
                2 => [
                    'student_fn' => 'Anna',
                    'student_ln' => 'Alpha',
                    'course_subject' => 'MATH',
                    'course_number' => '100',
                    'grade' => '71',
                    'points' => '2',
                    'course' => NULL,
                    'student' => NULL,
                ],
            ],
            'courses' => [
                0 => [
                    'course_subject' => 'ENGL',
                    'course_number' => '100',
                    'title' => 'Composition',
                    'enrollments' => NULL,
                    'students' => NULL,
                ],
                1 => [
                    'course_subject' => 'HIST',
                    'course_number' => '100',
                    'title' => 'World History',
                    'enrollments' => NULL,
                    'students' => NULL,
                ],
                2 => [
                    'course_subject' => 'MATH',
                    'course_number' => '100',
                    'title' => 'Algebra',
                    'enrollments' => NULL,
                    'students' => NULL,
                ],
            ],
            'engl_enrollments' => null,
        ],
        1 => [
            'student_fn' => 'Betty',
            'student_ln' => 'Beta',
            'degree_type' => 'MA',
            'degree_subject' => 'HIST',
            'gpa' => [
                'student_fn' => 'Betty',
                'student_ln' => 'Beta',
                'gpa' => '1.667',
                'student' => NULL,
            ],
            'degree' => [
                'degree_type' => 'ma',
                'degree_subject' => 'hist',
                'title' => 'Master of Arts, History',
                'students' => NULL,
            ],
            'enrollments' => [
                0 => [
                    'student_fn' => 'Betty',
                    'student_ln' => 'Beta',
                    'course_subject' => 'ENGL',
                    'course_number' => '200',
                    'grade' => '74',
                    'points' => '2',
                    'course' => NULL,
                    'student' => NULL,
                ],
                1 => [
                    'student_fn' => 'Betty',
                    'student_ln' => 'Beta',
                    'course_subject' => 'HIST',
                    'course_number' => '100',
                    'grade' => '68',
                    'points' => '1',
                    'course' => NULL,
                    'student' => NULL,
                ],
                2 => [
                    'student_fn' => 'Betty',
                    'student_ln' => 'Beta',
                    'course_subject' => 'MATH',
                    'course_number' => '100',
                    'grade' => '71',
                    'points' => '2',
                    'course' => NULL,
                    'student' => NULL,
                ],
            ],
            'courses' => [
                0 => [
                    'course_subject' => 'ENGL',
                    'course_number' => '200',
                    'title' => 'Creative Writing',
                    'enrollments' => NULL,
                    'students' => NULL,
                ],
                1 => [
                    'course_subject' => 'HIST',
                    'course_number' => '100',
                    'title' => 'World History',
                    'enrollments' => NULL,
                    'students' => NULL,
                ],
                2 => [
                    'course_subject' => 'MATH',
                    'course_number' => '100',
                    'title' => 'Algebra',
                    'enrollments' => NULL,
                    'students' => NULL,
                ],
            ],
            'engl_enrollments' => null,
        ],
        2 => [
            'student_fn' => 'Clara',
            'student_ln' => 'Clark',
            'degree_type' => 'BS',
            'degree_subject' => 'MATH',
            'gpa' => [
                'student_fn' => 'Clara',
                'student_ln' => 'Clark',
                'gpa' => '2',
                'student' => NULL,
            ],
            'degree' => [
                'degree_type' => 'bs',
                'degree_subject' => 'math',
                'title' => 'Bachelor of Science, Mathematics',
                'students' => NULL,
            ],
            'enrollments' => [
                0 => [
                    'student_fn' => 'Clara',
                    'student_ln' => 'Clark',
                    'course_subject' => 'ENGL',
                    'course_number' => '200',
                    'grade' => '74',
                    'points' => '2',
                    'course' => NULL,
                    'student' => NULL,
                ],
                1 => [
                    'student_fn' => 'Clara',
                    'student_ln' => 'Clark',
                    'course_subject' => 'HIST',
                    'course_number' => '200',
                    'grade' => '77',
                    'points' => '2',
                    'course' => NULL,
                    'student' => NULL,
                ],
                2 => [
                    'student_fn' => 'Clara',
                    'student_ln' => 'Clark',
                    'course_subject' => 'MATH',
                    'course_number' => '100',
                    'grade' => '71',
                    'points' => '2',
                    'course' => NULL,
                    'student' => NULL,
                ],
            ],
            'courses' => [
                0 => [
                    'course_subject' => 'ENGL',
                    'course_number' => '200',
                    'title' => 'Creative Writing',
                    'enrollments' => NULL,
                    'students' => NULL,
                ],
                1 => [
                    'course_subject' => 'HIST',
                    'course_number' => '200',
                    'title' => 'US History',
                    'enrollments' => NULL,
                    'students' => NULL,
                ],
                2 => [
                    'course_subject' => 'MATH',
                    'course_number' => '100',
                    'title' => 'Algebra',
                    'enrollments' => NULL,
                    'students' => NULL,
                ],
            ],
            'engl_enrollments' => null,
        ],
    ];
}
