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

Note that this will write only the Row for that Record back to the database.
These methods will not do anything with the Related fields on the Record; you
will need to write them individually.

Note also that inserting a Record with an auto-incrementing primary key will
automatically update the Record with that last-inserted ID.

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

Note that this will write only the Row for each Record back to the database.
These methods will not do anything with the Related fields on each Record; you
will need to write them individually, perhaps as part of the same Transaction.
