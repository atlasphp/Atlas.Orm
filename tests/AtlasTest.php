<?php
namespace Atlas\Orm;

use Atlas\Orm\DataSource\Author\AuthorMapper;
use Atlas\Orm\DataSource\Reply\ReplyMapper;
use Atlas\Orm\DataSource\Summary\SummaryMapper;
use Atlas\Orm\DataSource\Summary\SummaryTable;
use Atlas\Orm\DataSource\Tag\TagMapper;
use Atlas\Orm\DataSource\Tagging\TaggingMapper;
use Atlas\Orm\DataSource\Thread\ThreadMapper;
use Atlas\Orm\DataSource\Thread\ThreadRecord;
use Atlas\Orm\DataSource\Thread\ThreadRecordSet;
use Aura\Sql\ExtendedPdo;

class AtlasTest extends \PHPUnit_Framework_TestCase
{
    protected $atlas;

    // The $expect* properties are at the end, because they are so long

    protected function setUp()
    {
        $atlasContainer = new AtlasContainer('sqlite::memory:');
        $atlasContainer->setMappers([
            AuthorMapper::CLASS,
            ReplyMapper::CLASS,
            SummaryMapper::CLASS,
            TagMapper::CLASS,
            ThreadMapper::CLASS,
            TaggingMapper::CLASS,
        ]);

        $connection = $atlasContainer->getConnectionLocator()->getDefault();
        $fixture = new SqliteFixture($connection);
        $fixture->exec();

        $this->atlas = $atlasContainer->getAtlas();
    }

    public function testNewRecord()
    {
        $actual = $this->atlas->newRecord(ThreadMapper::CLASS);
        $this->assertInstanceOf(ThreadRecord::CLASS, $actual);
    }

    public function testNewRecordSet()
    {
        $actual = $this->atlas->newRecordSet(ThreadMapper::CLASS);
        $this->assertInstanceOf(ThreadRecordSet::CLASS, $actual);
    }

    public function testFetchRecord()
    {
        $actual = $this->atlas->fetchRecord(
            ThreadMapper::CLASS,
            1,
            [
                'author', // manyToOne
                'summary', // oneToOne
                'replies' => function ($select) {
                    $select->with(['author']);
                }, // oneToMany
                'taggings', // oneToMany,
                'tags', // manyToMany
            ]
        );

        $this->assertSame($this->expectRecord, $actual->getArrayCopy());
    }

    public function testFetchRecordBy()
    {
        $actual = $this->atlas->fetchRecordBy(
            ThreadMapper::CLASS,
            ['thread_id' => 1],
            [
                'author', // manyToOne
                'summary', // oneToOne
                'replies' => function ($select) {
                    $select->with(['author']);
                }, // oneToMany
                'taggings', // oneToMany,
                'tags', // manyToMany
            ]
        );

        $this->assertSame($this->expectRecord, $actual->getArrayCopy());
    }

    public function testFetchRecordSet()
    {
        $actual = $this->atlas->fetchRecordSet(
            ThreadMapper::CLASS,
            [1, 2, 3],
            [
                'author', // manyToOne
                'summary', // oneToOne
                'replies' => function ($select) {
                    $select->with(['author']);
                }, // oneToMany
                'taggings', // oneToMany,
                'tags', // manyToMany
            ]
        )->getArrayCopy();

        foreach ($this->expectRecordSet as $i => $expect) {
            $this->assertSame($expect, $actual[$i], "record $i not the same");
        }
    }

    public function testFetchRecordSetBy()
    {
        $actual = $this->atlas->fetchRecordSetBy(
            ThreadMapper::CLASS,
            ['thread_id' => [1, 2, 3]],
            [
                'author', // manyToOne
                'summary', // oneToOne
                'replies' => function ($select) {
                    $select->with(['author']);
                }, // oneToMany
                'taggings', // oneToMany,
                'tags', // manyToMany
            ]
        )->getArrayCopy();

        foreach ($this->expectRecordSet as $i => $expect) {
            $this->assertSame($expect, $actual[$i], "record $i not the same");
        }
    }

    public function testSelect_fetchRecord()
    {
        $actual = $this->atlas
            ->select(ThreadMapper::CLASS)
            ->where('thread_id < ?', 2)
            ->with([
                'author', // manyToOne
                'summary', // oneToOne
                'replies' => function ($select) {
                    $select->with(['author']);
                }, // oneToMany
                'taggings', // oneToMany,
                'tags', // manyToMany
            ])
            ->fetchRecord();

        $this->assertSame($this->expectRecord, $actual->getArrayCopy());
    }

    public function testSelect_fetchRecordSet()
    {
        $actual = $this->atlas
            ->select(ThreadMapper::CLASS)
            ->where('thread_id < ?', 4)
            ->with([
                'author', // manyToOne
                'summary', // oneToOne
                'replies' => function ($select) {
                    $select->with(['author']);
                }, // oneToMany
                'taggings', // oneToMany,
                'tags', // manyToMany
            ])
            ->fetchRecordSet()
            ->getArrayCopy();

        foreach ($this->expectRecordSet as $i => $expect) {
            $this->assertSame($expect, $actual[$i], "record $i not the same");
        }
    }

    public function testInsert()
    {
        // create a new record
        $author = $this->atlas->mapper(AuthorMapper::CLASS)->newRecord();
        $author->name = 'Mona';

        // does the insert *look* successful?
        $success = $this->atlas->insert($author);
        $this->assertTrue($success);

        // did the autoincrement ID get retained?
        $this->assertEquals(13, $author->author_id);

        // did it save in the identity map?
        $again = $this->atlas->fetchRecord(AuthorMapper::CLASS, 13);
        $this->assertSame($author->getRow(), $again->getRow());

        // was it *actually* inserted?
        $expect = [
            'author_id' => '13',
            'name' => 'Mona',
        ];
        $actual = $this->atlas
            ->mapper(AuthorMapper::CLASS)
            ->getReadConnection()
            ->fetchOne(
                'SELECT * FROM authors WHERE author_id = 13'
            );
        $this->assertSame($expect, $actual);
    }

    public function testUpdate()
    {
        // fetch a record, then modify and update it
        $author = $this->atlas->fetchRecordBy(
            AuthorMapper::CLASS,
            ['name' => 'Anna']
        );
        $author->name = 'Annabelle';

        // did the update *look* successful?
        $success = $this->atlas->update($author);
        $this->assertTrue($success);

        // is it still in the identity map?
        $again = $this->atlas->fetchRecordBy(
            AuthorMapper::CLASS,
            ['name' => 'Annabelle']
        );
        $this->assertSame($author->getRow(), $again->getRow());

        // was it *actually* updated?
        $expect = $author->getRow()->getArrayCopy();
        $actual = $this->atlas
            ->mapper(AuthorMapper::CLASS)
            ->getReadConnection()
            ->fetchOne(
                "SELECT * FROM authors WHERE name = 'Annabelle'"
            );
        $this->assertSame($expect, $actual);

        // try to update again, should be a no-op because there are no changes
        $this->assertFalse($this->atlas->update($author));
    }

    public function testDelete()
    {
        // fetch a record
        $author = $this->atlas->fetchRecordBy(
            AuthorMapper::CLASS,
            ['name' => 'Anna']
        );

        // did the delete *look* successful?
        $success = $this->atlas->delete($author);
        $this->assertTrue($success);

        // was it *actually* deleted?
        $actual = $this->atlas->fetchRecordBy(
            AuthorMapper::CLASS,
            ['name' => 'Anna']
        );
        $this->assertFalse($actual);
    }

    public function testTransactionFailure()
    {
        // fetch a record
        $author = $this->atlas->fetchRecordBy(
            AuthorMapper::CLASS,
            ['name' => 'Anna']
        );

        // set to null, should fail update
        $author->name = null;
        $success = $this->atlas->update($author);
        $this->assertFalse($success);

        // get the exception
        $e = $this->atlas->getException();
        $this->assertInstanceOf('Exception', $e);
    }

    public function testCalcPrimary()
    {
        // plain old primary value
        $actual = $this->atlas->fetchRecord(AuthorMapper::CLASS, 1);
        $this->assertSame('1', $actual->author_id);

        // primary embedded in array
        $actual = $this->atlas->fetchRecord(AuthorMapper::CLASS, [
            'author_id' => 2,
            'foo' => 'bar',
            'baz' => 'dib'
        ]);
        $this->assertSame('2', $actual->author_id);

        // not a scalar
        $this->setExpectedException(
            'UnexpectedValueException',
            "Expected scalar value for primary key 'author_id', got array instead."
        );
        $this->atlas->fetchRecord(AuthorMapper::CLASS, [1, 2, 3]);
    }

    protected $expectRecord = [
        'thread_id' => '1',
        'author_id' => '1',
        'subject' => 'Thread subject 1',
        'body' => 'Thread body 1',
        'author' => [
            'author_id' => '1',
            'name' => 'Anna',
            'replies' => null,
            'threads' => null,
        ],
        'summary' => [
            'thread_id' => '1',
            'reply_count' => '5',
            'view_count' => '0',
            'thread' => null,
        ],
        'replies' => [
            0 => [
                'reply_id' => '1',
                'thread_id' => '1',
                'author_id' => '2',
                'body' => 'Reply 1 on thread 1',
                'author' => [
                    'author_id' => '2',
                    'name' => 'Betty',
                    'replies' => null,
                    'threads' => null,
                ],
            ],
            1 => [
                'reply_id' => '2',
                'thread_id' => '1',
                'author_id' => '3',
                'body' => 'Reply 2 on thread 1',
                'author' => [
                    'author_id' => '3',
                    'name' => 'Clara',
                    'replies' => null,
                    'threads' => null,
                ],
            ],
            2 => [
                'reply_id' => '3',
                'thread_id' => '1',
                'author_id' => '4',
                'body' => 'Reply 3 on thread 1',
                'author' => [
                    'author_id' => '4',
                    'name' => 'Donna',
                    'replies' => null,
                    'threads' => null,
                ],
            ],
            3 => [
                'reply_id' => '4',
                'thread_id' => '1',
                'author_id' => '5',
                'body' => 'Reply 4 on thread 1',
                'author' => [
                    'author_id' => '5',
                    'name' => 'Edna',
                    'replies' => null,
                    'threads' => null,
                ],
            ],
            4 => [
                'reply_id' => '5',
                'thread_id' => '1',
                'author_id' => '6',
                'body' => 'Reply 5 on thread 1',
                'author' => [
                    'author_id' => '6',
                    'name' => 'Fiona',
                    'replies' => null,
                    'threads' => null,
                ],
            ],
        ],
        'taggings' => [
            0 => [
                'tagging_id' => '1',
                'thread_id' => '1',
                'tag_id' => '1',
            ],
            1 => [
                'tagging_id' => '2',
                'thread_id' => '1',
                'tag_id' => '2',
            ],
            2 => [
                'tagging_id' => '3',
                'thread_id' => '1',
                'tag_id' => '3',
            ],
        ],
        'tags' => [
            0 => [
                'tag_id' => '1',
                'name' => 'foo',
                'taggings' => null,
                'threads' => null,
            ],
            1 => [
                'tag_id' => '2',
                'name' => 'bar',
                'taggings' => null,
                'threads' => null,
            ],
            2 => [
                'tag_id' => '3',
                'name' => 'baz',
                'taggings' => null,
                'threads' => null,
            ],
        ],
    ];

    protected $expectRecordSet = [
        0 => [
            'thread_id' => '1',
            'author_id' => '1',
            'subject' => 'Thread subject 1',
            'body' => 'Thread body 1',
            'author' => [
                'author_id' => '1',
                'name' => 'Anna',
                'replies' => null,
                'threads' => null,
            ],
            'summary' => [
                'thread_id' => '1',
                'reply_count' => '5',
                'view_count' => '0',
                'thread' => null,
            ],
            'replies' => [
                0 => [
                    'reply_id' => '1',
                    'thread_id' => '1',
                    'author_id' => '2',
                    'body' => 'Reply 1 on thread 1',
                    'author' => [
                        'author_id' => '2',
                        'name' => 'Betty',
                        'replies' => null,
                        'threads' => null,
                    ],
                ],
                1 => [
                    'reply_id' => '2',
                    'thread_id' => '1',
                    'author_id' => '3',
                    'body' => 'Reply 2 on thread 1',
                    'author' => [
                        'author_id' => '3',
                        'name' => 'Clara',
                        'replies' => null,
                        'threads' => null,
                    ],
                ],
                2 => [
                    'reply_id' => '3',
                    'thread_id' => '1',
                    'author_id' => '4',
                    'body' => 'Reply 3 on thread 1',
                    'author' => [
                        'author_id' => '4',
                        'name' => 'Donna',
                        'replies' => null,
                        'threads' => null,
                    ],
                ],
                3 => [
                    'reply_id' => '4',
                    'thread_id' => '1',
                    'author_id' => '5',
                    'body' => 'Reply 4 on thread 1',
                    'author' => [
                        'author_id' => '5',
                        'name' => 'Edna',
                        'replies' => null,
                        'threads' => null,
                    ],
                ],
                4 => [
                    'reply_id' => '5',
                    'thread_id' => '1',
                    'author_id' => '6',
                    'body' => 'Reply 5 on thread 1',
                    'author' => [
                        'author_id' => '6',
                        'name' => 'Fiona',
                        'replies' => null,
                        'threads' => null,
                    ],
                ],
            ],
            'taggings' => [
                0 => [
                    'tagging_id' => '1',
                    'thread_id' => '1',
                    'tag_id' => '1',
                ],
                1 => [
                    'tagging_id' => '2',
                    'thread_id' => '1',
                    'tag_id' => '2',
                ],
                2 => [
                    'tagging_id' => '3',
                    'thread_id' => '1',
                    'tag_id' => '3',
                ],
            ],
            'tags' => [
                0 => [
                    'tag_id' => '1',
                    'name' => 'foo',
                    'taggings' => null,
                    'threads' => null,
                ],
                1 => [
                    'tag_id' => '2',
                    'name' => 'bar',
                    'taggings' => null,
                    'threads' => null,
                ],
                2 => [
                    'tag_id' => '3',
                    'name' => 'baz',
                    'taggings' => null,
                    'threads' => null,
                ],
            ],
        ],
        1 => [
            'thread_id' => '2',
            'author_id' => '2',
            'subject' => 'Thread subject 2',
            'body' => 'Thread body 2',
            'author' => [
                'author_id' => '2',
                'name' => 'Betty',
                'replies' => null,
                'threads' => null,
            ],
            'summary' => [
                'thread_id' => '2',
                'reply_count' => '5',
                'view_count' => '0',
                'thread' => null,
            ],
            'replies' => [
                0 => [
                    'reply_id' => '6',
                    'thread_id' => '2',
                    'author_id' => '3',
                    'body' => 'Reply 1 on thread 2',
                    'author' => [
                        'author_id' => '3',
                        'name' => 'Clara',
                        'replies' => null,
                        'threads' => null,
                    ],
                ],
                1 => [
                    'reply_id' => '7',
                    'thread_id' => '2',
                    'author_id' => '4',
                    'body' => 'Reply 2 on thread 2',
                    'author' => [
                        'author_id' => '4',
                        'name' => 'Donna',
                        'replies' => null,
                        'threads' => null,
                    ],
                ],
                2 => [
                    'reply_id' => '8',
                    'thread_id' => '2',
                    'author_id' => '5',
                    'body' => 'Reply 3 on thread 2',
                    'author' => [
                        'author_id' => '5',
                        'name' => 'Edna',
                        'replies' => null,
                        'threads' => null,
                    ],
                ],
                3 => [
                    'reply_id' => '9',
                    'thread_id' => '2',
                    'author_id' => '6',
                    'body' => 'Reply 4 on thread 2',
                    'author' => [
                        'author_id' => '6',
                        'name' => 'Fiona',
                        'replies' => null,
                        'threads' => null,
                    ],
                ],
                4 => [
                    'reply_id' => '10',
                    'thread_id' => '2',
                    'author_id' => '7',
                    'body' => 'Reply 5 on thread 2',
                    'author' => [
                        'author_id' => '7',
                        'name' => 'Gina',
                        'replies' => null,
                        'threads' => null,
                    ],
                ],
            ],
            'taggings' => [
                0 => [
                    'tagging_id' => '4',
                    'thread_id' => '2',
                    'tag_id' => '2',
                ],
                1 => [
                    'tagging_id' => '5',
                    'thread_id' => '2',
                    'tag_id' => '3',
                ],
                2 => [
                    'tagging_id' => '6',
                    'thread_id' => '2',
                    'tag_id' => '4',
                ],
            ],
            'tags' => [
                0 => [
                    'tag_id' => '2',
                    'name' => 'bar',
                    'taggings' => null,
                    'threads' => null,
                ],
                1 => [
                    'tag_id' => '3',
                    'name' => 'baz',
                    'taggings' => null,
                    'threads' => null,
                ],
                2 => [
                    'tag_id' => '4',
                    'name' => 'dib',
                    'taggings' => null,
                    'threads' => null,
                ],
            ],
        ],
        2 => [
            'thread_id' => '3',
            'author_id' => '3',
            'subject' => 'Thread subject 3',
            'body' => 'Thread body 3',
            'author' => [
                'author_id' => '3',
                'name' => 'Clara',
                'replies' => null,
                'threads' => null,
            ],
            'summary' => [
                'thread_id' => '3',
                'reply_count' => '5',
                'view_count' => '0',
                'thread' => null,
            ],
            'replies' => [
                0 => [
                    'reply_id' => '11',
                    'thread_id' => '3',
                    'author_id' => '4',
                    'body' => 'Reply 1 on thread 3',
                    'author' => [
                        'author_id' => '4',
                        'name' => 'Donna',
                        'replies' => null,
                        'threads' => null,
                    ],
                ],
                1 => [
                    'reply_id' => '12',
                    'thread_id' => '3',
                    'author_id' => '5',
                    'body' => 'Reply 2 on thread 3',
                    'author' => [
                        'author_id' => '5',
                        'name' => 'Edna',
                        'replies' => null,
                        'threads' => null,
                    ],
                ],
                2 => [
                    'reply_id' => '13',
                    'thread_id' => '3',
                    'author_id' => '6',
                    'body' => 'Reply 3 on thread 3',
                    'author' => [
                        'author_id' => '6',
                        'name' => 'Fiona',
                        'replies' => null,
                        'threads' => null,
                    ],
                ],
                3 => [
                    'reply_id' => '14',
                    'thread_id' => '3',
                    'author_id' => '7',
                    'body' => 'Reply 4 on thread 3',
                    'author' => [
                        'author_id' => '7',
                        'name' => 'Gina',
                        'replies' => null,
                        'threads' => null,
                    ],
                ],
                4 => [
                    'reply_id' => '15',
                    'thread_id' => '3',
                    'author_id' => '8',
                    'body' => 'Reply 5 on thread 3',
                    'author' => [
                        'author_id' => '8',
                        'name' => 'Hanna',
                        'replies' => null,
                        'threads' => null,
                    ],
                ],
            ],
            'taggings' => [
            ],
            'tags' => [
            ],
        ],
    ];
}
