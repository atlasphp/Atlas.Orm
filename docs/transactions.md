# Transactions (Unit of Work)

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
