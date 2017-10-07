# Direct Queries

If you need to perform queries directly, additional `fetch*` and `yield*` methods
are provided which expose the Extended PDO functionality. By using the `$cols`
parameter, you can select specific columns or individual values. For example:

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

See [the list of `ExtendedPdo::fetch*()`][fetch] and [`yield*()`][yield]
methods for more.

[fetch]: https://github.com/auraphp/Aura.Sql/tree/2.x#new-fetch-methods
[yield]: https://github.com/auraphp/Aura.Sql/tree/2.x#new-yield-methods

You can also call `fetchRow()` or `fetchRows()` to get Row objects directly
from the Table underlying the Mapper.

## Fetching

### Fetch Value

Returns a single value, or null.

```php
<?php
$subject = $atlas
    ->select(ThreadMapper::CLASS)
    ->cols(['subject'])
    ->where('thread_id = ?', '1')
    ->fetchValue();

// "Subject One"
```

### Fetch Column

Returns a sequential array of one column, or an empty array.

```php
<?php
$subjects = $atlas
    ->select(ThreadMapper::CLASS)
    ->cols(['subject'])
    ->limit(2)
    ->fetchCol();

// [
//   0 => "Subject One",
//   1 => "Subject Two"
// ]
```

### Fetch Pairs

Returns an associative array where the key is the first column and the value is
the second column, or an empty array.

```php
<?php
$subjectAndBody = $atlas
    ->select(ThreadMapper::CLASS)
    ->cols(['subject', 'body'])
    ->limit(2)
    ->fetchPairs();

// [
//   'Subject One' => "Body Text One",
//   'Subject Two' => "Body Text Two"
// ]
```

### Fetch One

Returns an associative array of one row, or null.

```php
<?php
$threadData = $atlas
    ->select(ThreadMapper::CLASS)
    ->cols(['subject', 'body', 'author_id'])
    ->where('thread_id = 1')
    ->fetchOne();

// [
//   'subject' => "Subject One",
//   'body' => "Body Text One",
//   'author_id' => "1"
// ]
```

### Fetch Assoc

Returns an associative array of rows keyed on the first column specified, or an
empty array.

```php
<?php
$threads = $atlas
    ->select(ThreadMapper::CLASS)
    ->cols(['subject', 'body'])
    ->limit(2)
    ->fetchAssoc();

// [
//   'Subject One' => [
//     'subject' => "Subject One",
//     'body' => "Body Text One",
//   ],
//   'Subject Two' => [
//     'subject' => "Subject Two",
//     'body' => "Body Text Two"
//   ]
// ]
```

### Fetch All

Returns a sequential array of associative arrays, or an empty array.

```php
<?php
$threads = $atlas
    ->select(ThreadMapper::CLASS)
    ->cols(['subject', 'body'])
    ->limit(2)
    ->orderBy('thread_id DESC')
    ->fetchAll();

// [
//   0 => [
//     'subject' => "Subject One",
//     'body' => "Body Text One"
//   ],
//   1 => [
//     'subject' => "Subject Two",
//     'body' => "Body Text Two"
//   ]
// ]
```

## Yielding Data

If you prefer to get the results one at a time, you can use the `yield*`
variations on these methods to iterate through the result set instead of
returning an array.

### Yield Col

Iterate through a sequential array of one column.

```php
<?php
$subjects = $atlas
    ->select(ThreadMapper::CLASS)
    ->cols(['subject'])
    ->yieldCol();

foreach($subjects as $subject) {
    echo $subject;
}
```

### Yield Pairs

Iterate through an associative array by the first column specified.

```php
<?php
$subjectAndBody = $atlas
    ->select(ThreadMapper::CLASS)
    ->cols(['subject', 'body'])
    ->yieldPairs();

foreach($subjectAndBody as $subject => $body) {
    echo $subject . ": " . $body;
}
```

### Yield Assoc

Iterate through an associative array of rows by the first column specified.

```php
<?php
$threads = $atlas
    ->select(ThreadMapper::CLASS)
    ->cols(['thread_id', 'subject'])
    ->yieldAssoc();

foreach($threads as $threadId => $thread) {
    echo $threadId . ": " . $thread['subject'];
}
```

### Yield All

Iterate through a sequential array of rows.

```php
<?php
$threads = $atlas
    ->select(ThreadMapper::CLASS)
    ->cols(['thread_id', 'subject'])
    ->yieldAll();

 foreach($threads as $thread) {
    echo $thread['thread_id'] . ": " . $thread['subject'];
}
```

## Complex Queries

You can use any of the direct table access methods with more complex queries and
joins.

```php
<?php
$threadData = $atlas
    ->select(ThreadMapper::CLASS)
    ->cols(['threads.subject', 'authors.name', 's.*'])
    ->join('INNER', 'authors', 'authors.author_id = threads.author_id')
    ->join('INNER', 'summary s', 's.thread_id = threads.thread_id')
    ->where('authors.name = ?', $name)
    ->orderBy('threads.thread_id DESC')
    ->offset(2)
    ->limit(2)
    ->fetchAssoc();
```

## Reusing the Select

The select object can be used for multiple queries, which may be useful for
pagination.  The generated select statement can also be displayed for debugging
purposes.

```php
<?php
$select = $atlas
    ->select(ThreadMapper::CLASS)
    ->cols(['*'])
    ->offset(10)
    ->limit(5);

// Fetch the current result set
$results = $select->fetchAll();

// Fetch the row count without any limit or offset
$totalCount = $select->fetchCount();

// View the generated select statement
$statement = $select->getStatement();
```
