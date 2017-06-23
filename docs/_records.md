# Working With Records

Once you have a Record, you can access its underlying Row and Related data
as properties.

```php
<?php
// fetch thread id 1 with related replies, and each reply author
$threadRecord = $atlas->fetchRecord(
    ThreadMapper::CLASS,
    1,
    [
        'replies' => [
            'author',
        ],
    ]
);

echo $threadRecord->title;
echo $threadRecord->body;
foreach ($threadRecord->replies as $replyRecord) {
    echo $replyRecord->author->name;
    echo $replyRecord->body;
}
```

Make changes to the Record by setting new property values.

```php
<?php
$threadRecord->title = "Thread title";
$threadRecord->body = "Body text for the thread";
```

Note that the Row supporting each Record is identity-mapped, so a change to
a Row used by more than one Record will be reflected immediately in each
Record using that Row.

 ```php
<?php
// if the reply rows are different, but the author of each reply
// is the same, the reply author objects are the same.
$threadRecord
    ->replies[0]
    ->author
    ->name = "New name";

// $threadRecord->replies[1]->author->name is now also "New name"
```

## New Records

Create a new Record using the `newRecord()` method, optionally passing any data
you want to initially populate into the Record.

```php
<?php
$threadRecord = $atlas->newRecord(
    ThreadMapper::CLASS,
    [
        'title' => 'New Thread Title',
    ]
);
```

Note that this does not insert the Record into the database.

## JSON Encoding

JSON-encoding a Record is trivial:

```php
<?php
$json = json_encode($threadRecord);
```
