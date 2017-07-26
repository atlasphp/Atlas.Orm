# Working with Records

## Creating and Inserting a Record

Create a new Record using the `newRecord()` method. You can assign data using
properties, or pass an array of initial data to populate into the Record.

```php
<?php
$thread = $atlas->newRecord(ThreadMapper::CLASS,
    [
        'title'=>'New Thread Title',
    ]
);
```

You can assign a value via a property, which maps to a column name.

```php
<?php
$date = new \DateTime();
$thread->date_added = $date->format('Y-m-d H:i:s');
```

You can insert a single Record back to the database by using the `Atlas::insert()`
method. This will use the appropriate Mapper for the Record to perform the
write within a transaction, and capture any exceptions that occur along the way.

```php
<?php
$success = $atlas->insert($thread);
if ($success) {
    echo "Wrote the Record back to the database.";
} else {
    echo "Did not write the Record: " . $atlas->getException();
}
```

Inserting a Record with an auto-incrementing primary key will automatically
modify the Record to set the last-inserted ID.

Inserting a Record will automatically set the foreign key fields on the native
Record, and on all the loaded relationships for that Record.

In the following example, assume a Thread Record has a `manyToOne` relationship
with an Author Record using the `author_id` column. The relationship is named
`author`. (See the section on relationships for more information.)

```php
<?php
$author = $atlas->fetchRecord(AuthorMapper::CLASS, 4);
$thread = $atlas->newRecord(ThreadMapper::CLASS,
    [
        'title'=>'New Thread Title',
        'author'=>$author
    ]
);
// If the insert is successful, the `author_id` column will automatically be
// set to the Author Record's primary key value. In this case, 4.
$success = $atlas->insert($thread);

echo $thread->author_id; // 4
```

> **Note:**
  If the Author Record is new, Atlas will NOT automatically insert the
  new Author and set the foreign key on the new Author Record via the `insert()`
  method. This can, however, be achieved using the `persist()` method. This is
  discussed later in this chapter.

The following will fail.

```php
<?php
$author = $atlas->newRecord(AuthorMapper::CLASS,
    [
        'first_name'=>'Sterling',
        'last_name'=>'Archer'
    ]
);
$thread = $atlas->newRecord(ThreadMapper::CLASS,
    [
        'title'=>'New Thread Title',
        'author'=>$author
    ]
);
// Insert will not create the related Author Record. Use persist() instead.
$success = $atlas->insert($thread);
```

## Updating an Existing Record

Updating an existing record works the same as `insert()`.

```php
<?php
// fetch an existing record by primary key
$thread = $atlas->fetchRecord(ThreadMapper::CLASS, 3);

// Modify the title
$thread->title = 'This title is better than the last one';

// Save the record back to the database.
$success = $atlas->update($thread);
if ($success) {
    echo "Wrote the Record back to the database.";
} else {
    echo "Did not write the Record: " . $atlas->getException();
}
```

As with `insert()`, foreign keys are also updated, but only for existing related
records.

```php
<?php
$thread = $atlas->fetchRecord(ThreadMapper::CLASS, 3);
$author = $atlas->fetchRecord(AuthorMapper::CLASS, 4);

// Modify the author
$thread->author = $author;

// Save the record back to the database.
$success = $atlas->update($thread);
if ($success) {
    echo "Wrote the Record back to the database.";
} else {
    echo "Did not write the Record: " . $atlas->getException();
}
```

## Deleting a Record

Deleting a record works the same as inserting or updating.

```php
<?php
$thread = $atlas->fetchRecord(ThreadMapper::CLASS, 3);

$success = $atlas->delete($thread);
if ($success) {
    echo "Removed record from the database.";
} else {
    echo "Did not remove the Record: " . $atlas->getException();
}
```

## Persisting a Record

If you like, you can persist a Record and all of its loaded relationships (and
all of *their* loaded relationships, etc.) back to the database using the Atlas
`persist()` method. This is good for straightforward relationship structures
where the order of write operations does not need to be closely managed.

The `persist()` method will:

- persist many-to-one and many-to-many relateds loaded on the native Record;
- persist the native Record by ...
    - inserting the Row for the Record if it is new; or,
    - updating the Row for the Record if it has been modified; or,
    - deleting the Row for the Record if the Record has been marked for deletion
      using the Record::markForDeletion() method;
- persist one-to-one and one-to-many relateds loaded on the native Record.

```php
<?php
$success = $atlas->persist($record);
if ($success) {
    echo "Wrote the Record and all of its relateds back to the database.";
} else {
    echo "Did not write the Record: " . $atlas->getException();
}
```

As with insert and update, this will automatically set the foreign key fields on
the native Record, and on all the loaded relationships for that Record.

If a related field is not loaded, it cannot be persisted automatically.

Note that whether or not the Row for the Record is inserted/updated/deleted, the
`persist()` method will still recursively traverse all the related fields and
persist them as well.

The `delete()` method **will not** attempt to cascade deletion or nullification
across relateds at the ORM level. Your database may have cascading set up at the
database level; Atlas has no control over this.

## Marking Records for Deletion

You may also mark records for deletion and they will be removed from the database
after a transaction is completed via `persist()`.

```php
<?php
$thread = $atlas->fetchRecord(ThreadMapper::CLASS, 3);
// Mark the record for deletion
$thread->markForDeletion();
$atlas->persist($thread);
```

You can also mark several related Records for deletion and when the native
Record is persisted, they will be deleted from the database.

```php
<?php
// Assume a oneToMany relationship between a thread and its comments
// Select the thread and related comments
$thread = $atlas->fetchRecord(ThreadMapper::CLASS, 3,
    [
        'comments'
    ]
);

// Mark each related comment for deletion
foreach ($thread->comments as $comment) {
    $comment->markForDeletion();
}

// Persist the thread and the comments are also deleted
$atlas->persist($thread);

```
