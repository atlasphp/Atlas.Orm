# Working With RecordSets

Once you have a RecordSet, you can iterate over it.

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

## Array Access

The RecordSet also acts as an array, so you can get/set/unset Records by their
sequential keys in the RecordSet.

```php
<?php
// address the second record in the set
$threadRecordSet[1]->title = 'Changed Title';

// unset the first record in the set
unset($threadRecordSet[0]);

// push a new record onto the set
$threadRecordSet[] = $atlas->newRecord(ThreadMapper::CLASS);
```

## Appending

You can append a new Record using `appendNew()`, optionally passing any
data you want to initially populate into the Record:

```php
<?php
$newThread = $threadRecordSet->appendNew([
    'title' => 'New Title',
]);
```

Note that this only adds the Record to the RecordSet; it does not insert the
Record into the database.

## Searching

You can search for Records by their column values:

```php
<?php
// returns one matching Record object from the RecordSet,
// or false if there is no match
$matchingRecord = $threadRecordSet->getOneBy(['subject' => 'Subject One']);

// returns an array of matching Record objects from the RecordSet
$matchingRecords = $threadRecordSet->getAllBy(['author_id' => '5']);
```

## Removing

You can remove Records by their column values.

```php
<?php
// unsets and returns one matching Record from the Record Set,
// or false if there is no match
$removedRecord = $threadRecordSet->removeOneBy(['subject' => 'Subject One']);

// unsets and returns an array of matching Record objects from the Record Set
$removedRecords = $threadRecordSet->removeAllBy(['author_id' => '5']);
```

Note that this only removes them from the RecordSet; it does not delete them
from the database.

## New RecordSets

Create a new RecordSet using the `newRecordSet()` method.

```php
<?php
$threadRecordSet = $atlas->newRecordSet(ThreadMapper::CLASS);
```

## JSON Encoding

JSON-encoding a RecordSet is trivial:

```php
<?php
$json = json_encode($threadRecordSet);
```

## Other Methods

Other RecordSet methods include:

```php
<?php
$array = $recordSet->getArrayCopy();
$isEmpty = empty($threadRecordSet);
$count = count($threadRecordSet);
```