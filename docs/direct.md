# Direct Queries

If you need to perform queries directly, additional `fetch*` and `yield*`
methods are provided which expose the underlying _Atlas\Pdo\Connection_
functionality. By using the `columns()` method, you can select specific columns
or individual values. For example:

```php
// an array of IDs
$threadIds = $atlas
    ->select(Thread::CLASS)
    ->columns('thread_id')
    ->limit(10)
    ->orderBy('thread_id DESC')
    ->fetchColumn();

// key-value pairs of IDs and titles
$threadIdsAndTitles = $atlas
    ->select(Thread::CLASS)
    ->columns('thread_id', 'title')
    ->limit(10)
    ->orderBy('thread_id DESC')
    ->fetchKeyPair();

// etc.
```

See the list of _Connection_ [fetch()][fetch] and [yield()][yield]
methods for more.

[fetch]: https://github.com/atlasphp/Atlas.Pdo/blob/1.x/docs/connection.md#fetching-results
[yield]: https://github.com/atlasphp/Atlas.Pdo/blob/1.x/docs/connection.md#yielding-results

You can also call `fetchRow()` or `fetchRows()` to get Row objects directly
from the Table underlying the Mapper.


## Complex Queries

You can use any of the direct table access methods with more complex queries and
joins as provided by [Atlas.Query][]:

```php
$threadData = $atlas
    ->select(Thread::CLASS)
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

## Joining On Defined Relationships

In addition the various `JOIN` methods provided by Atlas.Query, the
_MapperSelect_ also provides `joinWith()`, so that you can join on a defined
relationship and then use columns from that relationship. (The related table
will be aliased automatically as the relationship name.)

For example, to `JOIN` with another table as defined in the Mapper
relationships:

```php
$threadIdsAndAuthorNames = $atlas
    ->select(Thread::CLASS)
    ->joinWith('author')
    ->columns(
        "threads.thread_id",
        "CONCAT(author.first_name, ' ', author.last_name)"
    )
    ->limit(10)
    ->orderBy('thread_id DESC')
    ->fetchKeyPair();
```

You can specify the JOIN type as part of the related name string, in addition
to an alias of your choosing:

```php
// specify the join type:
$select->joinWith('LEFT author');

// specify an alternative alias:
$select->joinWith('author AS author_alias');

// specify both
$select->joinWith('LEFT author AS author_alias');
```

Finally, you can pass a callable as an optional third parameter to add "sub"
JOINs on the already-joined relationship. For example, to find all authors
with threads that have the "foo" tag on them:

```php
$authorsWithThreadsAndTags = $atlas
    ->select(Author::CLASS)
    ->joinWith('threads', function ($sub) {
        $sub->joinWith('taggings', function ($sub) {
            $sub->joinWith('tag');
        });
    })
    ->where('tag = ', 'foo');
```

This builds a query similar to the following:

```sql
SELECT
    *
FROM
    authors
        JOIN threads ON authors.author_id = threads.author_id
        JOIN taggings ON threads.thread_id = taggings.thread_id
        JOIN tags AS tag ON taggings.tag_id = tag.tag_id
WHERE tag = :__1__
```

> **Note:**
>
> Using `joinWith()` **does not** select any records from the defined
> relationship; it only adds a JOIN clause. If you want to select related
> records, use the `with()` method.


## Reusing the Select

The select object can be used for multiple queries, which may be useful for
pagination.  The generated select statement can also be displayed for debugging
purposes.

```php
$select = $atlas
    ->select(Thread::CLASS)
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
