# Atlas

> No schema discovery. No migrations. No annotations. No lazy loading. No domain models. No opinions. Just data mapping.

Atlas is an ORM for your **persistence** (or **data source**) model, not for your domain model. Use Atlas data source objects to populate your domain model objects.

**ATLAS IS A WORK IN PROGRESS. FOR ENTERTAINMENT PURPOSES ONLY. DO NOT USE IN PRODUCTION OR EVEN IN SIDE PROJECTS. BREAKING CHANGES ARE GUARANTEED.**

* * *

Atlas works in 2 layers. The lower _Table_ layer is a table data gatway implementation:

- A _Row_ represents a single table row.

- A _RowSet_ represents a collection of _Row_ objects.

- A _Table_ acts as a gateway to a single SQL table to select _Row_ and _RowSet_ objects from that table, and insert/update/delete _Row_ objects in that table.

- A _RowFilter_ acts as a validator and sanitizer on _Row_ data for inserts and updates.

The upper _Mapper_ layer is a Data Mapper implementation **for the persistence model**. As such, Atlas uses the term "record" to indicate that its objects are *not* domain entities. Note that this is a *passive* record, not an active record; you do not add behaviors to it.

- A _Record_ combines a single _Row_ object with its related _Record_ and _RecordSet_ objects.

- A _RecordSet_ is a collection of _Record_ objects.

- A _Mapper_ wraps _Row_ and _RowSet_ objects from a _Table_ in _Record_ and _RecordSet_ objects. It also handles relationships to other _Mapper_ objects.

Finally, an _Atlas_ object acts as a collection point for all _Mapper_ objects, allowing you to work with them as a cohesive whole.

* * *

Create your data source classes by hand, or use a skeleton generator in the directory where you want the classes to be created:

```bash
cd src/App/DataSource
php ../../bin/atlas-skeleton App\\DataSource\\Author
```

> N.b.: No database connection is needed. You can hand-edit the files afterwards as necessary; some sensible defaults are applied.

That creates this directory and these empty extended classes in `src/App/DataSource/`:

    └── Author
        ├── AuthorMapper.php
        ├── AuthorRecord.php
        ├── AuthorRecordSet.php
        ├── AuthorRow.php
        ├── AuthorRowFilter.php
        ├── AuthorRowSet.php
        └── AuthorTable.php

Do that once for each SQL table in your database.

Then create an _Atlas_ instance using the _AtlasContainer_:

```php
<?php
$atlasContainer = new AtlasContainer('mysql');
$atlasContainer->setDefaultConnection(function () {
    return new ExtendedPdo(
        'mysql:host=localhost;dbname=testdb',
        'username',
        'password'
    );
});

$atlasContainer->setMappers([
    AuthorMapper::CLASS,
    ReplyMapper::CLASS,
    SummaryMapper::CLASS,
    TagMapper::CLASS,
    ThreadMapper::CLASS,
    Thread2TagMapper::CLASS,
]);

$atlas = $atlasContainer->getAtlas();
?>
```

Then you can use Atlas to select a _Record_ or a _RecordSet_:

```php
<?php
// fetch thread_id 1, with the top 10 replies in descending order,
// including each reply author
$threadRecord = $atlas->fetchRecord(ThreadMapper::CLASS, '1', [
    'author',
    'summary',
    'replies' => function ($select) {
        $select->limit(10)->with(['author']);
    },
    'threads2tags',
    'tags',
]);


// fetch thread_id 1, 2, and 3, with the top 10 replies in descending order,
// including each reply author
$threadRecordSet = $atlas->fetchRecordSet(ThreadMapper::CLASS, [1, 2, 3], [
    'author',
    'summary',
    'replies' => function ($select) {
        $select->limit(10)->with(['author']);
    },
    'threads2tags',
    'tags',
]);

// a more complex select of only the last 10 threads, with only some relateds
$threadRecordSet = $atlas
    ->select(ThreadMapper::CLASS)
    ->orderBy('thread_id DESC')
    ->with([
        'author',
        'summary'
    ])
    ->fetchRecordSet();
?>

If you do not load a _Record_ "with" a related, it will not be present in the _Record, and it will not be lazy-loaded for you later. This means you need to think ahead as to exactly what you will need from the database.

You can then address the _Record_'s underlying _Row_ columns and the related fields as properties.

```php
<?php
echo $thread->title;
echo $thread->body;
foreach ($thread->reples as $reply) {
    echo $reply->author->name;
    echo $reply->body;
}
?>
```
