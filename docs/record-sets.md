# Working with RecordSets

## Searching within RecordSets

You can search for Records by their column values:

```php
<?php
// returns one matching Record object from the RecordSet,
// or false if there is no match
$matchingRecord = $threadRecordSet->getOneBy(['subject' => 'Subject One']);

// returns an array of matching Record objects from the RecordSet
$matchingRecords = $threadRecordSet->getAllBy(['author_id' => '5']);
```

## Removing Records from RecordSets

You can remove Records from a RecordSet by their column values. This does NOT
delete the Record from the database; only from the RecordSet.

```php
<?php
// unsets and returns one matching Record from the Record Set,
// or false if there is no match
$removedRecord = $threadRecordSet->removeOneBy(['subject' => 'Subject One']);

// unsets and returns an array of matching Record objects from the Record Set
$removedRecords = $threadRecordSet->removeAllBy(['author_id' => '5']);
```

Note that this only removes them from the RecordSet; it does not delete them
from the database. If you need to delete a record from the database, see the
section on Marking Records for Deletion.

## Appending Records to a RecordSet

You can append a new Record to an existing RecordSet using `appendNew()`, optionally passing any
data you want to initially populate into the Record:

```php
<?php
$newThread = $threadRecordSet->appendNew([
    'title' => 'New Title',
]);
```
