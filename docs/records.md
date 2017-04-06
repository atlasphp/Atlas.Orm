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
$threadRecord = $atlas->newRecord(ThreadMapper::CLASS);
$threadRecord->title = "Thread title";
$threadRecord->body = "Body text for the thread";
```

## New Records

Create a new Record using the `newRecord()` method, optionally passing any data
you want to initially populate into the Record.

```php
<?php
$threadRecord = $atlas->newRecord(ThreadMapper::CLASS, [
    'title' => 'New Thread Title',
]);
```

Note that this does not insert the Record into the database.

## JSON Encoding

JSON-encoding a Record is trivial:

```php
<?php
$json = json_encode($threadRecord);
```

## Identity Mapping

Note that the Row supporting each Record is identity-mapped, so a change to
a Row used by more than one Record will be reflected immediately in each
Record using that Row.

 ```php
<?php
// $reply1 and $reply2 are two different replies by the same author. the reply
// rows are different, but the underlying author row is the same.

$reply1->author->name = "New name";

// $reply2->author->name is now also "New name"
```
