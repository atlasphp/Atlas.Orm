# Working with RecordSets

## New RecordSets

Create a new RecordSet using the `newRecordSet()` method.

```php
$threadRecordSet = $atlas->newRecordSet(Thread::CLASS);
```

## Appending Records to a RecordSet

You can append a new Record to an existing RecordSet using `appendNew()`,
optionally passing any data you want to initially populate into the Record:

```php
$newThread = $threadRecordSet->appendNew([
    'title' => 'New Title',
]);
```

Additionally, you can append foreign Records to a native Record's relateds.

```php
$thread = $atlas->fetchRecord(Thread::CLASS, 1, [
    'comments',
]);

$comment = $thread->comments->appendNew([
    'thread' => $thread,
    'comment' => 'Lorem ipsum dolor sit amet...'
]);

// insert the new comment directly
$atlas->insert($comment);

// or persist the whole thread
$atlas->persist($thread);
```

## Array Access

The RecordSet also acts as an array, so you can get/set/unset Records by their
sequential keys in the RecordSet.

```php
// address the second record in the set
$threadRecordSet[1]->title = 'Changed Title';

// unset the first record in the set
unset($threadRecordSet[0]);

// push a new record onto the set
$threadRecordSet[] = $atlas->newRecord(Thread::CLASS);
```

## Searching within RecordSets

You can search for Records within an existing RecordSet by their column values:

```php
$threadRecordSet = $atlas->select(Thread::CLASS)
    ->where('published = ', 1)
    ->fetchRecordSet();

// returns one matching Record object from the RecordSet,
// or null if there is no match
$matchingRecord = $threadRecordSet->getOneBy(['subject' => 'Subject One']);

// returns a new RecordSet of matching Record objects from the RecordSet
$matchingRecordSet = $threadRecordSet->getAllBy(['author_id' => '5']);
```

## Detaching Records from RecordSets

You can detach Records from a RecordSet by their column values. This does NOT
delete Records from the database; it only detaches them from the RecordSet.

```php
// unsets and returns one matching Record from the Record Set,
// or null if there is no match
$detachedRecord = $threadRecordSet->detachOneBy(['subject' => 'Subject One']);

// unsets and returns a new RecordSet of matching Record objects
$detachedRecordSet = $threadRecordSet->detachAllBy(['author_id' => '5']);

// unsets and returns a new RecordSet of all Record objects
$detachedRecordSet = $threadRecordSet->detachAll();
```

> **Note:**
  This only detaches them from the RecordSet; it does not delete them
  from the database. If you need to delete a Record from the database, see the
  sections on Marking Records for Deletion and deleting Records.

## Marking RecordSets For Deletion

You can mark each Record currently in a RecordSet for deletion by using the
`setDelete()` method:

```php
// mark all current records for deletion
$threadRecordSet->setDelete();
```

> **Note**:
>
> If you add another Record to the collection at this point, it will not have
> been marked for deletion.

You might only want to mark some of the Records for deletion:

```php
$threadRecordSet->getAllBy(['author_id' => 1])->setDelete();
```

When you persist a RecordSet relationship, all of its Records marked for
deletion will automatically be detached.

## Persisting A RecordSet

You can persist each Record in a RecordSet by calling the _Atlas_ method
`persistRecordSet()`:

```php
$atlas->persistRecordSet($threadRecordSet);
```

This will insert, update, or delete each Record in the RecordSet as appropriate.
