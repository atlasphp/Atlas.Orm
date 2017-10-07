# Working with RecordSets

## New RecordSets

Create a new RecordSet using the `newRecordSet()` method.

```php
<?php
$threadRecordSet = $atlas->newRecordSet(ThreadMapper::CLASS);
```

## Appending Records to a RecordSet

You can append a new Record to an existing RecordSet using `appendNew()`, optionally passing any
data you want to initially populate into the Record:

```php
<?php
$newThread = $threadRecordSet->appendNew([
    'title' => 'New Title',
]);
```

Additionally, you can append foreign Records to a native Record's relateds.

```php
<?php
$thread = $atlas->fetchRecord(ThreadMapper::CLASS, 1, [
    'comments',
]);

// Ensure we have a RecordSet to append to
if (! $thread->comments) {
    $thread->comments = $atlas->newRecordSet(CommentMapper::CLASS);
}

$comment = $thread->comments->appendNew([
    'thread_id' => $thread->thread_id,
    'comment' => 'Lorem ipsum dolor sit amet...'
]);

// plan to insert the new comment
$transaction->insert($comment);

// Or persist the thread
$atlas->persist($thread);
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

## Searching within RecordSets

You can search for Records within an existing RecordSet by their column values:

```php
<?php
$threadRecordSet = $atlas->select(ThreadMapper::CLASS)
    ->where('published=?', 1)
    ->fetchRecordSet();

// returns one matching Record object from the RecordSet,
// or null if there is no match
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
// or null if there is no match
$removedRecord = $threadRecordSet->removeOneBy(['subject' => 'Subject One']);

// unsets and returns an array of matching Record objects from the Record Set
$removedRecords = $threadRecordSet->removeAllBy(['author_id' => '5']);
```

> **Note:**
  This only removes them from the RecordSet; it does not delete them
  from the database. If you need to delete a record from the database, see the
  sections on Marking Records for Deletion and deleting Records.
