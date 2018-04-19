# Direct Queries

If you need to perform queries directly, additional `fetch*` and `yield*`
methods are provided which expose the underlying _Atlas\Pdo\Connection_
functionality. By using the `columns()` method, you can select specific columns
or individual values. For example:

```php
<?php
// an array of IDs
$threadIds = $atlas
    ->select(ThreadMapper::CLASS)
    ->columns(['thread_id'])
    ->limit(10)
    ->orderBy('thread_id DESC')
    ->fetchColumn();

// key-value pairs of IDs and titles
$threadIdsAndTitles = $atlas
    ->select(ThreadMapper::CLASS)
    ->columns(['thread_id', 'tite'])
    ->limit(10)
    ->orderBy('thread_id DESC')
    ->fetchKeyPair();

// etc.
```

See the list of _Connection_ [fetch*()][fetch] and [yield*()][yield]
methods for more.

[fetch]: https://github.com/atlasphp/Atlas.Pdo/blob/1.x/docs/connection.md#fetching-results
[yield]: https://github.com/atlasphp/Atlas.Pdo/blob/1.x/docs/connection.md#yielding-results

You can also call `fetchRow()` or `fetchRows()` to get Row objects directly
from the Table underlying the Mapper.


## Complex Queries

You can use any of the direct table access methods with more complex queries and
joins as provided by [Atlas.Query][]:

```php
<?php
$threadData = $atlas
    ->select(ThreadMapper::CLASS)
    ->columns('threads.subject', 'authors.name', 's.*')
    ->join('INNER', 'authors', 'authors.author_id = threads.author_id')
    ->join('INNER', 'summary AS s', 's.thread_id = threads.thread_id')
    ->where('authors.name = ', $name)
    ->orderBy('threads.thread_id DESC')
    ->offset(2)
    ->limit(2)
    ->fetchUnique();
```

[Atlas.Query]: https://github.com/atlasphp/Atlas.Query/blob/1.x/docs/select.md

In addition the various `JOIN` methods provided by Atlas.Query,
the _MapperSelect_ also provides `joinWith()`, so that you can join on a
defined relationship. (The related table will be aliased as the relationship
name.) For example, to do an `INNER JOIN` with another table as defined in
the Mapper relationships:

```php
<?php
$threadIdsAndAuthorNames = $atlas
    ->select(ThreadMapper::CLASS)
    ->joinWith('INNER', 'author')
    ->columns(
        "thread.thread_id",
        "CONCAT(author.first_name, ' ', author.last_name)"
    )
    ->limit(10)
    ->orderBy('thread_id DESC')
    ->fetchKeyPair();
```

## Reusing the Select

The select object can be used for multiple queries, which may be useful for
pagination.  The generated select statement can also be displayed for debugging
purposes.

```php
<?php
$select = $atlas
    ->select(ThreadMapper::CLASS)
    ->columns('*')
    ->offset(10)
    ->limit(5);

// Fetch the current result set
$results = $select->fetchAll();

// Fetch the row count without any limit or offset
$totalCount = $select->fetchCount();

// View the generated select statement
$statement = $select->getStatement();
```
