<?php
namespace Atlas;

class SqliteFixture
{
    protected $connection;

    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    public function exec()
    {
        $this->employees();
        $this->authors();
        $this->tags();
        $this->threads();
        $this->summaries();
        $this->threads2tags();
        $this->replies();
    }

    protected function employees()
    {
        $this->connection->query("CREATE TABLE employees (
            id       INTEGER PRIMARY KEY AUTOINCREMENT,
            name     VARCHAR(50) NOT NULL UNIQUE,
            building INTEGER,
            floor    INTEGER
        )");

        $stm = "INSERT INTO employees (name, building, floor) VALUES (?, ?, ?)";
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
            name VARCHAR(50) NOT NULL
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
            name VARCHAR(50) NOT NULL
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

    protected function threads2tags()
    {
        $this->connection->query("CREATE TABLE threads2tags (
            thread2tag_id INTEGER PRIMARY KEY AUTOINCREMENT,
            thread_id INTEGER NOT NULL,
            tag_id INTEGER NOT NULL
        )");

        // add 3 tags to each thread
        $stm = "INSERT INTO threads2tags (thread_id, tag_id) VALUES (?, ?)";
        for ($i = 0; $i < 20; $i ++) {
            $thread_id = $i + 1;
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
}
