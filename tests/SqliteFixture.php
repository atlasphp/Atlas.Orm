<?php
namespace Atlas\Orm;

class SqliteFixture
{
    protected $connection;

    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    public function exec()
    {
        $this->employee();
        $this->authors();
        $this->tags();
        $this->threads();
        $this->summaries();
        $this->taggings();
        $this->replies();

        $this->degrees();
        $this->students();
        $this->courses();
        $this->enrollments();
        $this->gpas();
    }

    protected function employee()
    {
        $this->connection->query("CREATE TABLE employee (
            id       INTEGER PRIMARY KEY AUTOINCREMENT,
            name     VARCHAR(10) NOT NULL UNIQUE,
            building INTEGER,
            floor    INTEGER
        )");

        $stm = "INSERT INTO employee (name, building, floor) VALUES (?, ?, ?)";
        $rows = [
            ['Anna',  1, 1],
            ['Betty', 1, 2],
            ['Clara', 1, 3],
            ['Donna', 1, 1],
            ['Edna',  1, 2],
            ['Fiona', 1, 3],
            ['Gina',  2, 1],
            ['Hanna', 2, 2],
            ['Ione',  2, 3],
            ['Julia', 2, 1],
            ['Kara',  2, 2],
            ['Lana',  2, 3],
        ];
        foreach ($rows as $row) {
            $this->connection->perform($stm, $row);
        }
    }

    protected function authors()
    {
        $this->connection->query("CREATE TABLE authors (
            author_id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(10) NOT NULL
        )");

        $stm = "INSERT INTO authors (name) VALUES (?)";
        $rows = [
            ['Anna'],
            ['Betty'],
            ['Clara'],
            ['Donna'],
            ['Edna'],
            ['Fiona'],
            ['Gina'],
            ['Hanna'],
            ['Ione'],
            ['Julia'],
            ['Kara'],
            ['Lana'],
        ];
        foreach ($rows as $row) {
            $this->connection->perform($stm, $row);
        }
    }

    protected function tags()
    {
        $this->connection->query("CREATE TABLE tags (
            tag_id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(10) NOT NULL
        )");

        $stm = "INSERT INTO tags (name) VALUES (?)";
        $rows = [
            ['foo'],
            ['bar'],
            ['baz'],
            ['dib'],
            ['zim'],
        ];
        foreach ($rows as $row) {
            $this->connection->perform($stm, $row);
        }
    }

    protected function threads()
    {
        $this->connection->query("CREATE TABLE threads (
            thread_id INTEGER PRIMARY KEY AUTOINCREMENT,
            author_id INTEGER NOT NULL,
            subject VARCHAR(255) NOT NULL,
            body TEXT NOT NULL
        )");

        $stm = "INSERT INTO threads (author_id, subject, body) VALUES (?, ?, ?)";
        for ($i = 0; $i < 20; $i ++) {
            $author_id = $i % 4 + 1; // first 4 people have 5 threads each
            $thread_id = $i + 1;
            $subject = "Thread subject {$thread_id}";
            $body = "Thread body {$thread_id}";
            $this->connection->perform($stm, [$author_id, $subject, $body]);
        }
    }

    protected function summaries()
    {
        $this->connection->query("CREATE TABLE summaries (
            thread_id INTEGER PRIMARY KEY,
            reply_count INTEGER NOT NULL DEFAULT 0,
            view_count INTEGER NOT NULL DEFAULT 0
        )");

        $stm = "INSERT INTO summaries (thread_id) VALUES (?)";
        for ($i = 0; $i < 20; $i ++) {
            $thread_id = $i + 1;
            $this->connection->perform($stm, [$thread_id]);
        }
    }

    protected function taggings()
    {
        $this->connection->query("CREATE TABLE taggings (
            tagging_id INTEGER PRIMARY KEY AUTOINCREMENT,
            thread_id INTEGER NOT NULL,
            tag_id INTEGER NOT NULL
        )");

        // add 3 tags to each thread except thread #3
        $stm = "INSERT INTO taggings (thread_id, tag_id) VALUES (?, ?)";
        for ($i = 0; $i < 20; $i ++) {
            $thread_id = $i + 1;
            if($thread_id == 3) {
                continue;
            }
            $tags = [
                (($i + 0) % 5) + 1,
                (($i + 1) % 5) + 1,
                (($i + 2) % 5) + 1,
            ];
            foreach ($tags as $tag_id) {
                $this->connection->perform($stm, [$thread_id, $tag_id]);
            }
        }
    }

    protected function replies()
    {
        $this->connection->query("CREATE TABLE replies (
            reply_id INTEGER PRIMARY KEY AUTOINCREMENT,
            thread_id INTEGER NOT NULL,
            author_id INTEGER NOT NULL,
            body TEXT
        )");

        // add 5 replies to each thread
        $stm = "INSERT INTO replies (thread_id, author_id, body) VALUES (?, ?, ?)";
        for ($thread_id = 1; $thread_id <= 20; $thread_id ++) {
            for ($i = 0; $i <= 4; $i ++) {
                $author_id = (($thread_id + $i) % 10) + 1;
                $reply_no = $i + 1;
                $body = "Reply {$reply_no} on thread {$thread_id}";
                $this->connection->perform($stm, [$thread_id, $author_id, $body]);
                $this->connection->perform("
                    UPDATE summaries
                    SET reply_count = reply_count + 1
                    WHERE thread_id = {$thread_id}
                ");
            }
        }
    }

    protected function degrees()
    {
        $this->connection->query("CREATE TABLE degrees (
            degree_type CHAR(2) CONSTRAINT dtnocase COLLATE NOCASE,
            degree_subject CHAR(4) CONSTRAINT dsnocase COLLATE NOCASE,
            title VARCHAR(50),
            PRIMARY KEY (degree_type, degree_subject)
        )");

        $stm = "INSERT INTO degrees (degree_type, degree_subject, title) VALUES (?, ?, ?)";
        $this->connection->perform($stm, ['ba', 'engl', 'Bachelor of Arts, English']);
        $this->connection->perform($stm, ['ma', 'hist', 'Master of Arts, History']);
        $this->connection->perform($stm, ['bs', 'math', 'Bachelor of Science, Mathematics']);
    }

    protected function students()
    {
        $this->connection->query("CREATE TABLE students (
            student_fn VARCHAR(10),
            student_ln VARCHAR(10),
            degree_type CHAR(2),
            degree_subject CHAR(4),
            PRIMARY KEY (student_fn, student_ln)
        )");

        $stm = "INSERT INTO students (student_fn, student_ln, degree_type, degree_subject) VALUES (?, ?, ?, ?)";
        $rows = [
            ['Anna', 'Alpha', 'BA', 'ENGL'],
            ['Betty', 'Beta', 'MA', 'HIST'],
            ['Clara', 'Clark', 'BS', 'MATH'],
            ['Donna', 'Delta', 'BA', 'ENGL'],
            ['Edna', 'Epsilon', 'MA', 'HIST'],
            ['Fiona', 'Phi', 'BS', 'MATH'],
            ['Gina', 'Gamma', 'BA', 'ENGL'],
            ['Hanna', 'Eta', 'MA', 'HIST'],
            ['Ione', 'Iota', 'BS', 'MATH'],
            ['Julia', 'Jones', 'BA', 'ENGL'],
            ['Kara', 'Kappa', 'MA', 'HIST'],
            ['Lana', 'Lambda', 'BS', 'MATH'],
        ];
        foreach ($rows as $row) {
            $this->connection->perform($stm, $row);
        }
    }

    protected function courses()
    {
        $this->connection->query("CREATE TABLE courses (
            course_subject CHAR(4),
            course_number INT,
            title VARCHAR(20),
            PRIMARY KEY (course_subject, course_number)
        )");

        $stm = "INSERT INTO courses (course_subject, course_number, title) VALUES (?, ?, ?)";
        $rows = [
            ['ENGL', 100, 'Composition'],
            ['ENGL', 200, 'Creative Writing'],
            ['ENGL', 300, 'Shakespeare'],
            ['ENGL', 400, 'Dickens'],
            ['HIST', 100, 'World History'],
            ['HIST', 200, 'US History'],
            ['HIST', 300, 'Victorian History'],
            ['HIST', 400, 'Recent History'],
            ['MATH', 100, 'Algebra'],
            ['MATH', 200, 'Trigonometry'],
            ['MATH', 300, 'Calculus'],
            ['MATH', 400, 'Statistics'],
        ];
        foreach ($rows as $row) {
            $this->connection->perform($stm, $row);
        }
    }

    protected function enrollments()
    {
        $this->connection->query("CREATE TABLE enrollments (
            student_fn VARCHAR(10),
            student_ln VARCHAR(10),
            course_subject CHAR(4),
            course_number INT,
            grade INT,
            points INT,
            PRIMARY KEY (student_ln, student_fn, course_subject, course_number)
        )");

        $courses = $this->connection->fetchAll('SELECT * FROM courses ORDER BY course_number, course_subject');
        $students = $this->connection->fetchAll('SELECT * FROM students');

        $stm = 'INSERT INTO enrollments (
            student_fn, student_ln, course_subject, course_number, grade, points
        ) VALUES (
            :student_fn, :student_ln, :course_subject, :course_number, :grade, :points
        )';

        foreach ($students as $i => $student) {
            $keys = [
                (($i + 0) % 12),
                (($i + 1) % 12),
                (($i + 2) % 12),
            ];
            foreach ($keys as $key) {
                $grade = 65 + $key * 3;
                switch (true) {
                    case $grade >= 90:
                        $points = 4;
                        break;
                    case $grade >= 80:
                        $points = 3;
                        break;
                    case $grade >= 70:
                        $points = 2;
                        break;
                    case $grade >= 60:
                        $points = 1;
                        break;
                    default:
                        $points = 0;
                }
                $this->connection->perform($stm, [
                    'student_fn' => $student['student_fn'],
                    'student_ln' => $student['student_ln'],
                    'course_subject' => $courses[$key]['course_subject'],
                    'course_number' => $courses[$key]['course_number'],
                    'grade' => $grade,
                    'points' => $points,
                ]);
            }
        }

        // foreach ($courses as $i => $course) {
        //     $keys = [
        //         (($i + 0) % 12),
        //         (($i + 1) % 12),
        //         (($i + 2) % 12),
        //         (($i + 3) % 12),
        //         (($i + 4) % 12),
        //         (($i + 5) % 12),
        //     ];
        //     foreach ($keys as $k => $key) {
        //         $grade = 65 + (($i + $k) * 2);
        //         switch (true) {
        //             case $grade >= 90:
        //                 $points = 4;
        //                 break;
        //             case $grade >= 80:
        //                 $points = 3;
        //                 break;
        //             case $grade >= 70:
        //                 $points = 2;
        //                 break;
        //             case $grade >= 60:
        //                 $points = 1;
        //                 break;
        //             default:
        //                 $points = 0;
        //         }
        //         $this->connection->perform($stm, [
        //             'student_fn' => $students[$key]['student_fn'],
        //             'student_ln' => $students[$key]['student_ln'],
        //             'course_subject' => $course['course_subject'],
        //             'course_number' => $course['course_number'],
        //             'grade' => $grade,
        //             'points' => $points,
        //         ]);
        //     }
        // }
    }

    public function gpas()
    {
        $this->connection->query("CREATE TABLE gpas (
            student_fn VARCHAR(10),
            student_ln VARCHAR(10),
            gpa DECIMAL(4,3),
            PRIMARY KEY (student_fn, student_ln)
        )");

        $students = $this->connection->fetchAll(
            'SELECT student_fn, student_ln, ROUND(AVG(points), 3) AS gpa FROM enrollments GROUP BY student_fn, student_ln'
        );

        $stm = 'INSERT INTO gpas (student_fn, student_ln, gpa) VALUES (?, ?, ?)';
        foreach ($students as $student) {
            $this->connection->perform($stm, [
                $student['student_fn'],
                $student['student_ln'],
                $student['gpa']
            ]);
        }
    }
}
