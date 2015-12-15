# Atlas.Orm

> No migrations. No annotations. No lazy loading. No domain models. No data-type abstractions. No behaviors. No opinions. Just data mapping.

Atlas is an ORM for your **persistence** (or **data source**) model, not for your domain model. Use Atlas data source objects to populate your domain model objects.

**ATLAS IS A WORK IN PROGRESS. FOR ENTERTAINMENT PURPOSES ONLY. DO NOT USE IN PRODUCTION OR EVEN IN SIDE PROJECTS. BREAKING CHANGES ARE GUARANTEED.**


## Rationale

Per [this article from Mehdi Khalili](http://www.mehdi-khalili.com/orm-anti-patterns-part-4-persistence-domain-model/), we're targeting "Domain Model composed of Persistence Model". That is, the domain Entities and Aggregates use data source Records and RecordSets internally, but never expose them. They can manipulate the persistence model internally as much as they wish. E.g., an Entity might have "getAddress()" and read from the internal Record (which in turn reads from its internal Row).

Alternatively, we can do "DDD on top of ORM" where Repositories map the data source Records to domain Entities, Value Objects, and Aggregates.


## Installation

This package is installable and autoloadable via [Composer](https://getcomposer.org/) as [atlas/atlas](https://
packagist.org/packages/atlas/atlas).
Make sure that you’ve set up your project to [autoload Composer-installed packages](https://getcomposer.org/doc/00-intro.md#autoloading).


## Operation

Atlas works in 2 layers. The lower _Table_ layer is a [table data gateway](http://martinfowler.com/eaaCatalog/tableDataGateway.html) implementation:

- A _Row_ represents a single table row.

- A _RowSet_ represents a collection of _Row_ objects.

- A _Table_ acts as a gateway to a single SQL table to select _Row_ and _RowSet_ objects from that table, and insert/update/delete _Row_ objects in that table.

- A _RowFilter_ acts as a validator and sanitizer on _Row_ data for inserts and updates.

The upper _Mapper_ layer is a [data mapper](http://martinfowler.com/eaaCatalog/dataMapper.html) implementation **for the persistence model**. As such, Atlas uses the term "record" to indicate that its objects are *not* domain entities. Note that this is a *passive* record, not an [active record](http://martinfowler.com/eaaCatalog/activeRecord.html); you do not add behaviors to it, and it is disconnected from the database.

- A _Record_ combines a single _Row_ object with its related _Record_ and _RecordSet_ objects.

- A _RecordSet_ is a collection of _Record_ objects.

- A _Mapper_ wraps _Row_ and _RowSet_ objects from a _Table_ in _Record_ and _RecordSet_ objects. It also handles relationships to other _Mapper_ objects.

Finally, an _Atlas_ object acts as a collection point for all _Mapper_ objects, allowing you to work with them as a cohesive whole.

## Basic Usage

> This section is sorely incomplete.

### Creating Classes

You can create your data source classes by hand, but it's going to be tedious to do so. Instead, use the skeleton generator command. While you don't need a database connection, it will be convenient to connect to the database and let the generator read from it.

Create a PHP file to return an array of connection parameters suitable for PDO:

```php
<?php
// ./conn.php
return ['mysql:dbname=testdb;host=localhost', 'username', 'password'];
?>
```

You can then invoke the skeleton generator using that connection. Specify a target directory for the skeleton files if you like, and pass the namespace name for the data source classes. Pass an explicit table name to keep the generator from trying to guess the name.

```bash
./bin/atlas-skeleton.php --conn=./conn.php --dir=src/App/DataSource App\\DataSource\\Thread --table=threads
```

That will create this directory and these empty extended classes in `src/App/DataSource/`:

    └── Thread
        ├── ThreadMapper.php
        ├── ThreadMapperEvents.php
        ├── ThreadRecord.php
        ├── ThreadRecordSet.php
        ├── ThreadTable.php
        └── ThreadTableEvents.php

Do that once for each SQL table in your database.

You can add relationships on a _Record_ by editing its _Relations_ class:

```php
<?php
namespace Atlas\DataSource\Thread;

use App\DataSource\Author\AuthorMapper;
use App\DataSource\Summary\SummaryMapper;
use App\DataSource\Reply\ReplyMapper;
use App\DataSource\Tagging\TaggingMapper;
use App\DataSource\Tag\TagMapper;
use Atlas\Orm\Mapper\Mapper;

class ThreadMapper extends Mapper
{
    protected function defineRelations()
    {
        $this->manyToOne('author', AuthorMapper::CLASS);
        $this->oneToOne('summary', SummaryMapper::CLASS);
        $this->oneToMany('replies', ReplyMapper::CLASS);
        $this->oneToMany('taggings', TaggingMapper::CLASS);
        $this->manyToMany('tags', TagMapper::CLASS, 'taggings');
    }
}
?>
```

### Reading

Create an _Atlas_ instance using the _AtlasContainer_:

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

You can then use Atlas to select a _Record_ or a _RecordSet_:

```php
<?php
// fetch thread_id 1; with related replies, including each reply author
$threadRecord = $atlas->fetchRecord(ThreadMapper::CLASS, '1', [
    'author',
    'summary',
    'replies' => function ($select) {
        $select->with(['author']);
    },
    'threads2tags',
    'tags',
]);


// fetch thread_id 1, 2, and 3; with related replies, including each reply author
$threadRecordSet = $atlas->fetchRecordSet(ThreadMapper::CLASS, [1, 2, 3], [
    'author',
    'summary',
    'replies' => function ($select) {
        $select->with(['author']);
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
```

If you do not load a _Record_ "with" a related, it will be `null` in the _Record_, and it will not be lazy-loaded for you later. This means you need to think ahead as to exactly what you will need from the database.

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

### Changing

Make changes to the _Record_ by setting new property values.

```php
<?php
$thread = $atlas->newRecord(ThreadMapper::CLASS);
$thread->title = "Thread title";
$thread->body = "Body text for the thread";
?>
```

Note that each _Row_ supporting each _Record_ is identity-mapped, so a change to a _Row_ used by more than one _Record_ will be reflected immediately in each _Record_ using that _Row_.

 ```php
<?php
// $reply1 and $reply2 are two different replies by the same author. the reply
// rows are different, but the underlying author row is the same.
$reply1->author->name = "New name"; // $reply2->author->name is now also "New name"
?>
```

### Writing

After you make changes to a _Record_, you can write it back to the database using a unit-of-work _Transaction_. You can plan for _Record_s to be inserted, updated, and deleted, in whatever order you like, and then execute the entire transaction plan at once. Exceptions will cause a rollback.

```php
<?php
// create a transaction
$transaction = $atlas->newTransaction();

// plan work for the transaction
$transaction->insert($record1);
$transaction->update($record2);
$transaction->delete($record3);

// execute the transaction plan
$ok = $transaction->exec();
if ($ok) {
    echo "Transaction success.";
} else {
    // get the exception that was thrown in the transaction
    $exception = $transaction->getException();
    // get the work element that threw the exception
    $work = $transaction->getFailure();
    // some output
    echo "Transaction failure. ";
    echo $work->getLabel() . ' threw ' . $exception->getMessage();
}
?>
```
