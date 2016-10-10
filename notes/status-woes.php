Does it make sense to have the status on the Row, or on the Record?

It makes for some interesting (bad) situations where a record/row gets inserted/updated and its state changes, but then an exception gets thrown and the transaction gets rolled back. It means the in-memory objects are wildly out of sync with the database, losing maybe the user-related changes and/or the database-related changes.

It's almost as if we have to operate on a clone of each row/record, then take all the modified ones and force their new state back into the originals. And that can only happen *after* the transaction is committed.

Or if a transaction gets rolled back, go back through every row/record and mark it as out-of-sync (or something similar) and disallow further modification to it. IS_STALE ?

Would be good, then, to have a way to clear the identity map so we can re-fetch

And frankly, a Transaction does not operate on a Record. It operates on a Row; the Record is a proxy for the Row.

Incidentally, now that we have status on the rows, we know if something needs to be inserted/updated/deleted. No need to specify insert/update/delete, only add a record to the transaction.

Maybe an *additional* state, saying what the last DB status was. The in-memory status is one thing, the last attempted DB action is another, and whether the attempt was committed or not is yet another. Or add states: "INSERT_FAILED", "UPDATE_FAILED", "DELETE_FAILED".
