# Fetching Records and RecordSets

Use Atlas to retrieve a single Record, an array of Records, or a collection of
Records in a RecordSet, from the database.

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

> **Note:** If `fetchRecord()` does not find a match, it will return `null`.

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

See also the page on [working with Records](./records.html).

## Fetching An Array Of Records

The `fetchRecords()` method works the same as `fetchRecord()`, but returns an
array of Records.  It can be called either with primary keys, or with a
`select()` query.

```php
<?php
// fetch thread_id 1, 2, and 3
$threadRecordSet = $atlas->fetchRecords(
    ThreadMapper::CLASS,
    [1, 2, 3]
);

// This is identical to the example above, but uses the `select()` variation.
$threadRecordSet = $atlas
    ->select(ThreadMapper::CLASS)
    ->where('thread_id IN (?)', [1, 2, 3])
    ->fetchRecords();
```

To return all rows, use the `select()` variation as shown below.

```php
<?php
// Use the `select()` variation to fetch all records, optionally ordering the
// returned results

$threadRecordSet = $atlas
    ->select(ThreadMapper::CLASS)
    ->orderBy(['date_added DESC'])
    ->fetchRecords();
```

> **Tip:**
  The `select()` variation gives you access to all the underlying
  SQL query methods. See [Aura\SqlQuery](https://github.com/auraphp/Aura.SqlQuery/blob/3.x/docs/select.md)
  for more information.

## Fetching A RecordSet Collection

The `fetchRecordSet()` method works just the same as `fetchRecords()`, but
instead of returning an array of Records, it returns a RecordSet collection.

> **Note:**
  If `fetchRecordSet()` does not find any matches, it will return an empty
  RecordSet collection object. To check if the RecordSet contains
  any Records, call the `isEmpty()` method on the RecordSet.

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

See also the page on [working with RecordSets](./record-sets.html).

## Fetching Related Records

Any relationships that are set in the Mapper will appear as `null` in the Record
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
$threadRecord = $atlas->fetchRecord(ThreadMapper::CLASS, '1', [
    'taggings', // specify the "through" first ...
    'tags' // ... then the manyToMany
]);
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
$threadRecord = $atlas->fetchRecord(ThreadMapper::CLASS, '1', [
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
]);
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

If you specify `with()` on a one-to-one or many-to-one relationship that returns
no result, the related field will be populated with `false`. If you specify
`with()` on a one-to-many or many-to-many relationship that returns no result,
the field will be populated with an empty RecordSet collection.


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
