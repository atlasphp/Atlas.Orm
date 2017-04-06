# Reading Records From The Database

Use Atlas to retrieve a single Record, or many Records in a RecordSet, from
the database.

## Reading A Record

Use the `fetchRecord()` method to retrieve a single Record. It can be called
either by primary key, or with a `select()` query.

```php
<?php
// fetch by primary key thread_id = 1

$threadRecord = $atlas->fetchRecord(
    ThreadMapper::class,
    '1'
);

$threadRecord = $atlas
    ->select(ThreadMapper::class)
    ->where('thread_id = ?', '1')
    ->fetchRecord();
```

(Note that the `select()` variation gives you access to all the underlying
SQL query methods.)

If `fetchRecord()` does not find a match, it will return `false`.

## Reading A RecordSet

The `fetchRecordSet()` method works the same as `fetchRecord()`, but for
multiple Records.  It can be called either with primary keys, or with a
`select()` query.

```php
<?php
// fetch thread_id 1, 2, and 3

$threadRecordSet = $atlas->fetchRecordSet(
    ThreadMapper::CLASS,
    [1, 2, 3]
);

$threadRecordSet = $atlas
    ->select(ThreadMapper::CLASS)
    ->where('thread_id IN (?)', [1, 2, 3])
    ->fetchRecordSet();
```

(Note that the `select()` variation gives you access to all the underlying
SQL query methods.)

If `fetchRecordSet()` does not find any matches, it will return an empty array.


## Reading Relateds

Any relationships that are set in the Mapper will appear as `NULL` in the Record
object.  Related data will only be populated if it explicitly requested as part
of the fetch or select.

On a `fetch*()`, load relateds using a third argument: an array specifying
which related fields to retrieve.

```php
<?php
$threadRecord = $atlas->fetchRecord(
    ThreadMapper::CLASS,
    '1',
    [
        'author',
        'summary',
        'replies',
    ]
);

$threadRecordSet = $atlas->fetchRecordSet(
    ThreadMapper::CLASS,
    [1, 2, 3],
    [
        'author',
        'summary',
        'replies',
    ]
);
```

On a `select()`, load relateds using the `with()` method:

```php
$threadRecord = $atlas
    ->select(ThreadMapper::class)
    ->where('thread_id = ?', '1')
    ->with([
        'author',
        'summary',
        'replies',
    ])
    ->fetchRecord();

$threadRecordSet = $atlas
    ->select(ThreadMapper::CLASS)
    ->where('thread_id IN (?)', [1, 2, 3])
    ->with([
        'author',
        'summary',
        'replies',
    ])
    ->fetchRecordSet();
```

Relationships can be nested as deeply as needed. For example, to fetch the
author of each reply on each thread:

```php
<?php
$threadRecord = $this->atlas
    ->select(ThreadMapper::class)
    ->where('thread_id = ?', $threadId)
    ->with([
        'author',
        'summary',
        'replies' => [
            'author'
        ]
    ])
    ->fetchRecord();
```

Alternatively, you can pass a closure to exercise fine control over the query
that fetches the relateds:

```php
<?php
// fetch thread_id 1; with only the last 10 related replies in descending order;
// including each reply author
$threadRecord = $atlas->fetchRecord(
    ThreadMapper::CLASS,
    '1',
    [
        'author',
        'summary',
        'replies' => function ($selectReplies) {
            $selectReplies
                ->limit(10)
                ->orderBy(['reply_id DESC'])
                ->with([
                    'author'
                ]);
        },
    ]
);
```

## Reading Record Counts

If you use a `select()` to fetch a RecordSet with a `limit()` or `page()`, you
can re-use the select to get a count of how many Records would have been
returned. This can be useful for paging displays.

```php
<?php
$select = $atlas
    ->select(ThreadMapper::CLASS)
    ->with([
        'author',
        'summary',
        'replies'
    ])
    ->limit(10)
    ->offset(20);

$threadRecordSet = $select->fetchRecordSet();
$countOfAllThreads = $select->fetchCount();
```
