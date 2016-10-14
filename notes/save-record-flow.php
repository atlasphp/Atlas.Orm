Plan on a record:

    For each belongs-to
        Set the belongs-to key on the master Record
        No descent into the related, to avoid recursion
    For each has-one
        Plan to set foreign key on the related Record
        Plan on the related Record
    For each has-many-through // this one is extra tough, look at Solar
        For each record
            Plan on the related Record (the tag)
            Plan to set through-native-key on the through Record (the tagging)
            Plan to set through-foreign-key on the through Record (the tagging)
            Plan on the through Record
    For each has-many
        For each record
            Plan to set foreign key on the related Record
            Plan on the related Record

Plan on a row:

    if Row::isNew()
        plan an Insert
        return

    if Row::isDirty()
        plan an update
        return

    if Row::isTrash()
        plan a delete
        return

    isDeleted() and isClean() are no-op, so return

Does the plan have to be different for an Insert vs Update vs Delete of the master record? I would think so, at least on a Delete: need to delete or nullify the relateds. That means the relation-saver needs a copu of the IdentityMap.

* * *

When deleting a Record we have some trouble:

- Need to delete the relateds as well.

- Except the far-side of a many-to-many, which may belong to other records.

- If not all the relateds have been loaded into memory, they'll be orphaned in the database, which means we need to delete rows from the DB that may not be in memory right now.

- If we delete at the database and the row is still in memory, we need to check the IdentityMap and mark the deleted ones as "deleted."

Hm, or only bother deleting the ones in memory, and tell the user to set ON DELETE CASCADE (or SET NULL) at the DB level to remove the DB ones.

Cf. http://docs.sqlalchemy.org/en/rel_1_0/orm/cascades.html

* * *

The thing is, what really happens in the Transaction is we operate on *Rows*, not on Records. Perhaps we need a TransactionPlan (i.e., UnitOfWork) that lists what will be done to each row, then the Transaction makes it happen.

*And* the "Related" operations need to occur; i.e., setting foreign key values, etc.

