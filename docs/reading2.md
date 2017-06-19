# Reading Records and RecordSets

Use Atlas to retrieve a single Record, or many Records in a RecordSet, from the database.

## Reading a Record

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
SQL query methods. See [Aura\SqlQuery](https://github.com/auraphp/Aura.SqlQuery/blob/3.x/docs/select.md) for more information.)

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

// This is identical to the example above, but uses the `select()` variation.
$threadRecordSet = $atlas
    ->select(ThreadMapper::CLASS)
    ->where('thread_id IN (?)', [1, 2, 3])
    ->fetchRecordSet();
```

To return all rows, use the `select()` variation as shown below.

```php
<?php
// Use the `select()` variation to fetch all records, optionally ordering the
// returned results

$threadRecordSet = $atlas
    ->select(ThreadMapper::CLASS)
    ->orderBy(['date_added DESC'])
    ->fetchRecordSet();
```

(Note that the `select()` variation gives you access to all the underlying
SQL query methods.)

If `fetchRecordSet()` does not find any matches, it will return an empty array.
This is important as you cannot call RecordSet methods (see later in the
documentation) such as `appendNew()` or `getArrayCopy()` on an empty array. In
these situations, you must test for the empty array, and then instantiate a
new RecordSet, if necessary.

```php
<?php
$threadRecordSet = $atlas->fetchRecordSet(
    ThreadMapper::CLASS,
    [1, 2, 3]
);
if (! $threadRecordSet) {
    $threadRecordSet = $atlas->newRecordSet(ThreadMapper::CLASS);
}

$threadMapper->appendNew(...);
```

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

**Note:** when fetching a `manyToMany` relationship, you mush explicitly specify
both the association (join) table AND the `manyToMany` table. Additionally, you
must specify these relationships in the correct order.

```php
<?php
$threadRecord = $atlas->fetchRecord(
    ThreadMapper::CLASS,
    '1',
    [
        'taggings', // specify the join table first
        'tags' // then the manyToMany table
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

## Reading/Accessing Data
