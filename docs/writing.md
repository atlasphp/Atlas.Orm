# Writing Records To The Database

## Individual Writes

You can write a single Record back to the database by using the Atlas
`insert()`, `update()`, and `delete()` methods. These will use the appropriate
Mapper for the Record to perform the write within a transaction, and capture any
exceptions that occur along the way.

```php
<?php
$success = $atlas->insert($record); // or update(), or delete()
if ($success) {
    echo "Wrote the Record back to the database.";
} else {
    echo "Did not write the Record: " . $atlas->getException();
}
```

Inserting a Record with an auto-incrementing primary key will automatically
modify the Record to set the last-inserted ID.

Inserting or updating a Record will automatically set the foreign key fields on
the native Record, and on all the loaded relationships for that Record.

The `insert()`, `update()`, and `delete()` methods write only the one Row for
that Record back to the database. They will not automatically operate on related
fields.

## Persisting Related Records

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

## Unit of Work

If you make changes to several Records, you can write them back to the database
using a unit-of-work Transaction. You can plan for Records to be inserted,
updated, and deleted, in whatever order you like, and then execute the entire
transaction plan at once. Exceptions will cause a rollback.

```php
<?php
// create a transaction
$transaction = $atlas->newTransaction();

// plan work for the transaction
$transaction->insert($record1);
$transaction->update($record2);
$transaction->delete($record3);

// or persist an entire record and its relateds
$transaction->persist($record4);

// execute the transaction plan
$success = $transaction->exec();
if ($success) {

    echo "The Transaction succeeded!";

} else {

    // get the exception that was thrown in the transaction
    $e = $transaction->getException();

    // get the work element that threw the exception
    $work = $transaction->getFailure();

    // some output
    echo "The Transaction failed: ";
    echo $work->getLabel() . ' threw ' . $e->getMessage();
}
```
