# Working with Records and RecordSets

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

// Assign a value via a property, which maps to a column name.
$date = new \DateTime();
$thread->date_added = $date->format('Y-m-d H:i:s');
```
You can insert a single Record back to the database by using the Atlas `insert()`
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

**NOTE:** If the Author Record is new, Atlas will NOT automatically insert the
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
// fetch an exist record by primary key
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

@@@@@@@@@@@@@@@@@@@@@@ Need info on appending @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
