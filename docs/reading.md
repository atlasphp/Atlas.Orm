# Reading Records

Depending on how you want to access the data, there are a variety of methods to choose from.  To use the atlas relationships directly, use the FetchRecord and FetchRecordSet methods.

### Fetch Record
> Returns a Record object or false

Fetch Record is the simplest way to select a single row with mapped relationships.  It can be called either by primary key, or with select parameters.

```php
<?php
// fetch by primary key thread_id = 1 with related author
$threadRecord = $atlas->fetchRecord(ThreadMapper::class, '1', [
    'author',
]);

// fetch by primary key thread_id = 1 with related author
$threadRecord = $atlas
    ->select(ThreadMapper::class)
    ->where('thread_id = ?', '1')
    ->with(['author'])
    ->fetchRecord();
```

### Fetch Record Set
> Returns a RecordSet object or an empty array

Fetch Record Set works the same as Fetch Record for multiple rows.  It can be called either by primary key, or with select parameters.

```php
<?php
// fetch thread_id 1, 2, and 3 with related author
$threadRecordSet = $atlas->fetchRecordSet(ThreadMapper::CLASS, [1, 2, 3], [
    'author'
]);

// select only the last 10 threads where thread_id is greater than 3 with related author
$threadRecordSet = $atlas
    ->select(ThreadMapper::CLASS)
    ->orderBy('thread_id DESC')
    ->limit(10)
    ->where('thread_id > 3')
    ->with([
        'author'
    ])
    ->fetchRecordSet();
```

### Mapped Relationships
Any relationships that are set in the mapper will appear as NULL in the result object.  The data will only be populated if specified in the 'with' parameters.

Mapped relationships can be nested as deeply as needed:

```php
<?php
$threadRecord = $this->atlas
    ->select(ThreadMapper::class)
    ->where('thread_id = ?', $threadId)
    ->with([
        'replies' => [
            'authors' => [
                'threads'
            ]
        ]
    ])
    ->fetchRecord();
```

### Processing Returned Data
You can access the underlying row data of each Record as a property.

```php
<?php
echo $threadRecord->title;
echo $threadRecord->body;
foreach ($threadRecord->replies as $reply) {
    echo $reply->author->name;
    echo $reply->body;
}
```

You can manipulate the returned Record Set by getting or removing records by column values:
```php
<?php
// returns one matching Record object or false
$matchingRecord = $threadRecordSet->getOneBy(['subject' => 'Subject One']);

// returns an array of matching Record objects
$matchingRecordArray = $threadRecordSet->getAllBy(['author_id' => '5']);

// unsets and returns one matching Record or false
$removedRecord = $threadRecordSet->removeOneBy(['subject' => 'Subject One']);

// unsets and returns an array of matching Record objects
$removedRecordArray = $threadRecordSet->removeAllBy(['author_id' => '5']);
```

Standard utility methods are provided:

```php
<?php
$array = $records->getArrayCopy();
$isEmpty = empty($records);
$count = count($records);
$json = json_encode($records);
```

## Direct Table Access
If you need to perform queries directly, additional fetch* and yield* methods are provided which expose the Extended PDO functionality.  By using the cols parameter, you can select specific columns or individual values.

### Fetch Value
> Returns a single value or false

```php
<?php
$subject = $atlas
    ->select(ThreadMapper::CLASS)
    ->cols(['subject'])
    ->where('thread_id = ?', '1')
    ->fetchValue();

// Returns:
    // string(11) "Subject One"
```

### Fetch Col
> Returns a sequential array of one column or an empty array

```php
<?php
$subjects = $atlas
    ->select(ThreadMapper::CLASS)
    ->cols(['subject'])
    ->limit(2)
    ->fetchCol();

// Returns:
    // array(2) {
    //   [0] =>
    //   string(11) "Subject One"
    //   [1] =>
    //   string(11) "Subject Two"
    // }
```

### Fetch Pairs
> Returns an associative array of rows by the first column specified or an empty array

```php
<?php
$subjectAndBody = $atlas
    ->select(ThreadMapper::CLASS)
    ->cols(['subject', 'body'])
    ->limit(2)
    ->fetchPairs();

// Returns
    // array(2) {
    //   ['Subject One'] =>
    //   string(13) "Body Text One"
    //   ['Subject Two'] =>
    //   string(13) "Body Text Two"
    // }
```

### Fetch One
> Returns an associative array of one row or false

```php
<?php
$threadData = $atlas
    ->select(ThreadMapper::CLASS)
    ->cols(['subject', 'body', 'author_id'])
    ->where('thread_id = 1')
    ->fetchOne();

// Returns
    // array(3) {
    //   'subject' =>
    //   string(11) "Subject One"
    //   'body' =>
    //   string(13) "Body Text One"
    //   'author_id' =>
    //   string(1) "1"
    // }
```

### Fetch Assoc
> Returns an associative array of rows by the first column specified or an empty array

```php
<?php
$threads = $atlas
    ->select(ThreadMapper::CLASS)
    ->cols(['subject', 'body'])
    ->limit(2)
    ->fetchAssoc();

// Returns
    // array(2) {
    //   'Subject One' =>
    //   array(2) {
    //     'subject' =>
    //     string(11) "Subject One"
    //     'body' =>
    //     string(13) "Body Text One"
    //   }
    //   'Subject Two' =>
    //   array(2) {
    //     'subject' =>
    //     string(11) "Subject Two"
    //     'body' =>
    //     string(13) "Body Text Two"
    //   }
    // }
```

### Fetch All
> Returns a sequential array of associative arrays or an empty array

```php
<?php
$threads = $atlas
    ->select(ThreadMapper::CLASS)
    ->cols(['subject', 'body'])
    ->limit(2)
    ->orderBy('thread_id DESC')
    ->fetchAll();

// Returns
    // array(2) {
    //   [0] =>
    //   array(2) {
    //     'subject' =>
    //     string(11) "Subject One"
    //     'body' =>
    //     string(13) "Body Text One"
    //   }
    //   [1] =>
    //   array(2) {
    //     'subject' =>
    //     string(11) "Subject Two"
    //     'body' =>
    //     string(13) "Body Text Two"
    //   }
    // }
```

## Yield

If you prefer to get the results one at a time, you can use the yield* variations on these methods to iterate through the result set instead of returning an array.

### Yield Col
> Iterate through a sequential array of one column

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
> Iterate through an associative array by the first column specified

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
> Iterate through an associative array of rows by the first column specified

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
> Iterate through a sequential array of rows

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

## Reusing the Select
The select object can be used for multiple queries, which may be useful for pagination.  The generated select statement can also be displayed for debugging purposes.

```php
<?php
$select = $atlas
    ->select(ThreadMapper::CLASS)
    ->cols(['*'])
    ->offset(10)
    ->limit(5);

// Fetch the row count without any limit or offset
$totalCount = $select->fetchCount();

// Fetch the current result set
$results = $select->fetchAll();

// View the generated select statement
$statement = $select->getStatement();
```

## Complex Queries

You can use any of the direct table access methods with more complex queries and joins.

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
