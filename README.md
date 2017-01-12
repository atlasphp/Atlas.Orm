# Atlas.Orm

> No annotations. No migrations. No lazy loading. No data-type abstractions.

Atlas is a [data mapper](http://martinfowler.com/eaaCatalog/dataMapper.html)
implementation for your **persistence model** (*not* your domain model).

As such, Atlas uses the term "record" to indicate that its objects are *not*
domain entities. Note that an Atlas record is a *passive* record, not an [active
record](http://martinfowler.com/eaaCatalog/activeRecord.html); it is
disconnected from the database. Use Atlas records as a way to populate your
domain entities, or use them directly for simple data source interactions.

Atlas is ready for side-project and experimental use. Please send bug reports
and pull requests!


## Rationale

I wanted an alternative to Active Record that would allow you to get started
about as easily as Active Record for your *persistence* model, and then refactor
more easily towards a richer *domain* model as needed.

Using a table data gateway for the underlying table Rows, then composing them
into Records and RecordSets via a data mapper, does the trick. As you begin to
need simple behaviors, you can add them to the Record and RecordSet persistence
model objects. (Rows do not have behavior.) Your domain logic layer (e.g. a
service layer) can then use them as needed.

However, per [this article from Mehdi Khalili][mkap], the target end-state for
your modeling should eventually move toward "Domain Model composed of
Persistence Model". That is, the domain Entity and Aggregate classes might use
data source Records and RecordSets internally, but will not expose them. They
can manipulate the persistence model objects internally as much as they wish.
E.g., an Entity might have a `getAddress()`method and read from the internal
Record (which in turn reads from its internal Row or Related objects).
Alternatively, the end state might be "DDD on top of ORM" where Repositories map
the persistence model objects to domain Entities, Value Objects, and Aggregates.

A persistence model alone should get you a long way, especially at the beginning
of a project. Even so, the Row, Record, and RecordSet objects are disconnected
from the database, which should make the refactoring process a lot cleaner than
with Active Record.

[mkap]: http://www.mehdi-khalili.com/orm-anti-patterns-part-4-persistence-domain-model/

Other rationalizations, essentially based around things I *do not* want in an
ORM:

- No annotations. I want the code to be in code, not in comments.

- No migrations or other table-modification logic. Many ORMs read the PHP objects
and then create or modify tables from them. I want the persistence system to be
a *model* of the schema, not a *creator* of it. If I need a migration, I'll use
a tool specifically for migrations.

- No lazy-loading. Lazy-loading is seductive but eventually is more trouble than
it's worth; I don't want it to be available at all, so that it cannot accidently
be invoked.

- No data-type abstractions. I used to think data-type abstraction was great,
but it turns out to be another thing that's just not worth the cost. I want the
actual underlying database types to be exposed and available as much as
possible.

Possible deal-breakers for potential users:

- Atlas uses code generation, though only in a very limited way. I'm not a fan
of code generation myself, but it turns out to be useful for building the SQL
table classes. Each table is described as a PHP class, one that just returns
things like the table name, the column names, etc. That's the only class that
really gets generated by Atlas; the others are just empty extensions of parent
classes.

- Atlas uses base Row, Record, and RecordSet classes, instead of plain-old PHP
objects. If this were a domain modeling system, a base class would be
unacceptable. Because Atlas is a *persistence* modeling system, I think a base
class is less objectionable, but for some people that's going to be a real
problem.

Finally, Atlas supports **composite primary keys** and **composite foreign keys.**
Performance in these cases is sure to be slower, but it is in fact supported.


## Installation

This package is installable and autoloadable via [Composer](https://getcomposer.org/)
as [atlas/orm](https://packagist.org/packages/atlas/orm).

Atlas is still in development and it is possible that the API may break, so you
may want to lock your `composer.json` to a
[particular release](https://github.com/atlasphp/Atlas.Orm/releases):

```json
{
    "require": {
        "atlas/orm": "0.3.*@alpha"
    }
}
```

Of course, if you want to keep up with the most recent unreleased changes, you can do the following:

```json
{
    "require": {
        "atlas/orm": "@dev"
    }
}
```

For ease of development you can add [atlas/cli](https://packagist.org/packages/atlas/cli)
in the `require-dev` section of `composer.json` in the root of your project. This will
provide the `atlas-skeleton` command-line tool.

```json
{
    "require-dev": {
        "atlas/cli": "@dev"
    }
}
```

## Basic Usage

> This section is sorely incomplete.

### Creating Classes

You can create your data source classes by hand, but it's going to be tedious to
do so. Instead, use the `atlas-skeleton` command to read the table information
from the database.

Create a PHP file to return an array of connection parameters suitable for PDO:

```php
<?php
// /path/to/conn.php
return ['mysql:dbname=testdb;host=localhost', 'username', 'password'];
```

You can then invoke `atlas-skeleton` using that connection and a table name.
Specify a target directory for the skeleton files if you like, and pass the
namespace name for the data source classes.

```bash
./vendor/bin/atlas-skeleton.php \
    --dir=./src/App/DataSource \
    --conn=/path/to/conn.php \
    --table=threads \
    App\\DataSource\\Thread
```

> N.b.: Calling `atlas-skeleton` with `--conn` and `--table` will overwrite any
> existing Table class; this makes sense only because the Table class represents
> the table description at the database. No other existing files will ever be
> overwritten.

That will create this subdirectory and these classes in `./src/App/DataSource/`:

    └── Thread
        ├── ThreadMapper.php
        └── ThreadTable.php

The Mapper class will be essentially empty, and the Table class will contain a
description of the database table.

Do that once for each SQL table in your database.

> N.b.: By default, Atlas uses generic Record and RecordSet classes for
> table data. You can create custom Record and RecordSet classes passing
> `--full` to `atlas-skeleton`; the Mapper will use the custom classes if
> available, and fall back to the generic ones if not. (Custom Row classes are
> not available, and probably not desirable.)

### Relationships

You can add relationships by editing the _Mapper_ class:

```php
<?php
namespace Atlas\DataSource\Thread;

use App\DataSource\Author\AuthorMapper;
use App\DataSource\Summary\SummaryMapper;
use App\DataSource\Reply\ReplyMapper;
use App\DataSource\Tagging\TaggingMapper;
use App\DataSource\Tag\TagMapper;
use Atlas\Orm\Mapper\AbstractMapper;

class ThreadMapper extends AbstractMapper
{
    protected function setRelated()
    {
        $this->manyToOne('author', AuthorMapper::CLASS);
        $this->oneToOne('summary', SummaryMapper::CLASS);
        $this->oneToMany('replies', ReplyMapper::CLASS);
        $this->oneToMany('taggings', TaggingMapper::CLASS);
        $this->manyToMany('tags', TagMapper::CLASS, 'taggings');
    }
}
```

By default, in all relationships except many-to-one, the relationship will take
the primary key column(s) in the native table, and map to those same column
names in the foreign table. In the case of many-to-one, it is the reverse; that
is, the relationship will take the primary key column(s) in the foreign table,
and map to those same column names in the native table.

If you want to use different columns, call the `on()` method on the relationship.
For example, if the threads table uses `author_id`, but the authors table uses
just `id`, you can do this:

```php
<?php
$this->oneToOne('author', AuthorMapper::CLASS)
    ->on([
        // native (threads) column => foreign (authors) column
        'author_id' => 'id',
    ]);
```

Likewise, if a table uses a composite key, you can re-map the relationship on
multiple columns. If table `foo` has composite primary key columns of `acol` and
`bcol`, and it maps to table `bar` on `foo_acol` and `foo_bcol`, you would do
this:

```php
<?php
class FooMapper
{
    protected function setRelated()
    {
        $this->oneToMany('bars', BarMapper::CLASS)
            ->on([
                // native (foo) column => foreign (bar) column
                'acol' => 'foo_acol',
                'bcol' => 'foo_bcol',
            ]);
    }
}
```

### Instantiating

Create an Atlas instance using the AtlasContainer, and provide the default
ExtendedPdo connection parameters:

```php
<?php
$atlasContainer = new AtlasContainer(
    'mysql:host=localhost;dbname=testdb',
    'username',
    'password'
);
```

Next, set the available mapper classes, and get back an Atlas instance:

```php
<?php
$atlasContainer->setMappers([
    AuthorMapper::CLASS,
    ReplyMapper::CLASS,
    SummaryMapper::CLASS,
    TagMapper::CLASS,
    ThreadMapper::CLASS,
    TaggingMapper::CLASS,
]);

$atlas = $atlasContainer->getAtlas();
```

### Reading Records

You can then use Atlas to read a Record or a RecordSet from the database.

Use the `fetch*()` methods to work with primary keys ...

```php
<?php
// fetch thread_id 1; with related replies, including each reply author
$threadRecord = $atlas->fetchRecord(ThreadMapper::CLASS, '1', [
    'author',
    'summary',
    'replies' => function ($select) {
        $select->with(['author']);
    },
    'taggings',
    'tags',
]);

// fetch thread_id 1, 2, and 3; with related replies, including each reply author
$threadRecordSet = $atlas->fetchRecordSet(ThreadMapper::CLASS, [1, 2, 3], [
    'author',
    'summary',
    'replies' => function ($select) {
        $select->with(['author']);
    },
    'taggings',
    'tags',
]);
```

...  or use the `select()...->fetch*()` methods to work with query objects:

```php
<?php
// select only the last 10 threads, with only some relationships
$threadRecordSet = $atlas
    ->select(ThreadMapper::CLASS)
    ->orderBy('thread_id DESC')
    ->limit(10)
    ->with([
        'author',
        'summary'
    ])
    ->fetchRecordSet();
```

> N.b.: If you do not fetch or select "with" a relationship, the field will be
> `null` in the Record, and it will not be lazy-loaded for you later. This means
> you need to think ahead as to exactly what you will need from the database.

You can then address each Record's underlying Row fields, and its Related
fields, as properties.

```php
<?php
echo $thread->title;
echo $thread->body;
foreach ($thread->replies as $reply) {
    echo $reply->author->name;
    echo $reply->body;
}
```

#### Reading Non-Record Data

Incidentally, you can also use the Mapper to select non-Record values directly
from the database; the mapper selection tool exposes the underlying
`ExtendedPdo::fetch*()` and `yield*()` methods for your convenience.

```php
<?php
// an array of IDs
$threadIds = $atlas
    ->select(ThreadMapper::CLASS)
    ->cols(['thread_id'])
    ->limit(10)
    ->orderBy('thread_id DESC')
    ->fetchCol();

// key-value pairs of IDs and titles
$threadIdsAndTitles = $atlas
    ->select(ThreadMapper::CLASS)
    ->cols(['thread_id', 'tite'])
    ->limit(10)
    ->orderBy('thread_id DESC')
    ->fetchPairs();

// etc.
```

See [the list of `ExtendedPdo::fetch*()`][fetch] and ['yield*()'][yield]
methods for more.

[fetch]: https://github.com/auraphp/Aura.Sql#new-fetch-methods
[yield]: https://github.com/auraphp/Aura.Sql#new-yield-methods

You can also call `fetchRow()` or `fetchRows()` to get Row objects directly
from the Table underlying the Mapper.


#### Pagination

With Aura.SqlQuery object exposed you can easily create paginated results.
Below is an example to demonstrate the same.

```php
$query = $atlas->select(ThreadMapper::CLASS)
    // page number
    ->page($_GET['page'])
    // set the number of rows per page.
    ->paging(20);
$result = $query->fetchRecordSet();

$totalResult = clone $query;
$$totalResult->resetCols()
    ->cols(['COUNT(id)'])
    ->limit(1);
$total = $totalResult->cols(['COUNT(id)'])->fetchValue();
```

### Modifying Records

Make changes to the Record by setting new property values.

```php
<?php
$thread = $atlas->newRecord(ThreadMapper::CLASS);
$thread->title = "Thread title";
$thread->body = "Body text for the thread";
```

Note that the Row supporting each Record is identity-mapped, so a change to
a Row used by more than one Record will be reflected immediately in each
Record using that Row.

 ```php
<?php
// $reply1 and $reply2 are two different replies by the same author. the reply
// rows are different, but the underlying author row is the same.
$reply1->author->name = "New name"; // $reply2->author->name is now also "New name"
```

### Writing Records

#### Individual Writes

You can write a single Record back to the database by using the Atlas
`insert()`, `update()`, and `delete()` methods. These will use the appropriate
Mapper for the Record to perform the write within a transaction, and capture any
exceptions that occur along the way.

```php
<?php
$success = $atlas->insert($record); // or update(), or delete()
if ($success) {
    echo "Wrote the Record back to the database.";
} else {
    echo "Did not write the Record: " . $e;
}
```

Note that this will write only the Row for that Record back to the database.
These methods will not do anything with the Related fields on the Record; you
will need to write them individually.

#### Unit of Work

If you make changes to several Records, you can write them back to the database
using a unit-of-work Transaction. You can plan for Records to be inserted,
updated, and deleted, in whatever order you like, and then execute the entire
transaction plan at once. Exceptions will cause a rollback.

```php
<?php
// create a transaction
$transaction = $atlas->newTransaction();

// plan work for the transaction
$transaction->insert($record1);
$transaction->update($record2);
$transaction->delete($record3);

// execute the transaction plan
$success = $transaction->exec();
if ($success) {

    echo "The Transaction succeeded!";

} else {

    // get the exception that was thrown in the transaction
    $e = $transaction->getException();

    // get the work element that threw the exception
    $work = $transaction->getFailure();

    // some output
    echo "The Transaction failed: ";
    echo $work->getLabel() . ' threw ' . $e->getMessage();
}
```

Note that this will write only the Row for each Record back to the database.
These methods will not do anything with the Related fields on each Record; you
will need to write them individually, perhaps as part of the same Transaction.
