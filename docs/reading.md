# Fetching Records and RecordSets

Use Atlas to retrieve a single Record, or many Records in a RecordSet, from the database.

## Fetching a Record

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


> **Tip:** The `select()` variation gives you access to all the underlying
  SQL query methods. See [Aura\SqlQuery](https://github.com/auraphp/Aura.SqlQuery/blob/3.x/docs/select.md)
 for more information.

> **Note:** If `fetchRecord()` does not find a match, it will return `false`.

> **Warning:** If using the `select()` variation with the `cols()` method, be sure to include
  the table's primary key column(s) if you are fetching a Record. If using one
  of the other `fetch*()` methods outlined in the chapter on Direct Queries,
  then this isn't necessary. See below.

```php
<?php
// must include the primary key column (and author_id because of the
// where clause)
$threadRecord = $atlas
    ->select(ThreadMapper::class)
    ->where('author_id = ?', '2')
    ->cols(['thread_id', 'title', 'author_id'])
    ->fetchRecord();

// No need to include the primary key column
$threadRecord = $atlas
    ->select(ThreadMapper::class)
    ->where('author_id = ?', '2')
    ->cols(['title', 'author_id'])
    ->fetchOne();
```

### Accessing/Reading Record Data

Once you have a Record, you can access the columns via properties on the Record.
Assume a database column called `title`.

```php
<?php
echo $thread->title;
```

## Fetching A RecordSet

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

> **Tip:**
  The `select()` variation gives you access to all the underlying
  SQL query methods. See [Aura\SqlQuery](https://github.com/auraphp/Aura.SqlQuery/blob/3.x/docs/select.md)
  for more information.

> **Note:**
  If `fetchRecordSet()` does not find any matches, it will
  return an empty array. This is important as you cannot call RecordSet methods
  (see later in the documentation) such as `appendNew()` or `getArrayCopy()` on
  an empty array. In these situations, you must test for the empty array, and then
  instantiate a new RecordSet, if necessary. See below.

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

### Accessing/Reading RecordSet Data

RecordSets act as arrays of Records. As such, you can easily iterate over the
RecordSet and access the Records individually.

```php
<?php
// fetch the top 100 threads
$threadRecordSet = $atlas
    ->select(ThreadMapper::CLASS)
    ->orderBy(['thread_id DESC'])
    ->limit(100)
    ->fetchRecordSet();

foreach ($threadRecordSet as $threadRecord) {
    echo $threadRecord->title;
}
```

## Fetching Related Records

Any relationships that are set in the Mapper will appear as `NULL` in the Record
object.  Related data will only be populated if it is explicitly requested as part
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

When using the `select()` variation, load relateds using the `with()` method:

```php
<?php
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

> **Note:**
  When fetching a `manyToMany` relationship, you must explicitly specify
  both the association (through) related AND the `manyToMany` related.
  Additionally, you must specify these relationships in the correct order.

```php
<?php
$threadRecord = $atlas->fetchRecord(
    ThreadMapper::CLASS,
    '1',
    [
        'taggings', // specify the through first
        'tags' // then the manyToMany
    ]
);
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

### Accessing/Reading Related Data

Accessing related data works just like accessing Record properties except instead
of using a column name, you use the relationship name defined in the mapper.

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

// Assume the author table has a column named `last_name`
foreach ($threadRecord->replies as $reply) {
    echo $reply->author->last_name;
}
```


## Returning Data in Other Formats

You can return a Record or a RecordSet as an `array` rather than a Record or
RecordSet object using the `getArrayCopy()` method.

```php
<?php
$threadRecord = $atlas->fetchRecord('ThreadMapper::CLASS', '1');
$threadArray = $threadRecord->getArrayCopy();

$threadRecordSet = $atlas
    ->select(ThreadMapper::CLASS)
    ->orderBy(['date_added DESC'])
    ->fetchRecordSet();

$threadsArray = $threadRecordSet->getArrayCopy();
```

JSON-encoding Records and RecordSets is trival.

```php
<?php
$threadJson = json_encode($threadRecord);
$threadsJson = json_encode($threadRecordSet);
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
