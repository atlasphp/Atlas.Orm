Factories:
    
    Extract newRow(), newRowSet(), newRecord(), newRecordSet() to factory objects? Would allow for finer user control over how they get created, and might be future-proofing.

RowSet/RecordSet:

    Add "append()" or "addNew()" to Sets to append a new Row/Record of the proper type. Probably need to keep a reference back to the original Table/Mapper for the "new" logic. Alternatively, extract RowFactory and RecordFactory for use in the Sets. Hm, but that entails dealing with the IdentityMap as well.

Record:

    Add a RecordInterface (and a RecordSet interface?) so that Domain objects can typehint against the interface, and not necessarily an actual record. Maybe also add a <Type>RecordInterface class that extends RecordInterface, and have <Type>Record implement it.

Relationships:

    More tests for when relationships are missing.

    Have each Record and RecordSet note the record it "belongs to" and the foreign key mapping?

    Then in Record and RecordSet relations, automatically set "belongs to" foriegn key value on appendNew().

Compound primary keys:

    Allow for compound primaries.

    Build fetching strategies and identity lookups for compound keys.

Writing:

    Unit Of Work that allows you to attach Record objects.

    Single-record strategy to save a record and all of its relateds. Probably
    uses a Unit Of Work under the hood.

Skeleton generator:

    Allow for ...

        --table={tablename}
        --primary={primarycol}
        --autoinc={bool}

    ... to specify pertinent values. It also means different templates for different classes.

    Also allow for `--dir={dir}` so you don't need to `cd` into the right directory.

* * *

What we're going for is "Domain Model composed of Persistence Model". That is, the Domain entities/aggregates use Records and RecordSets internally, but never expose them. They can manipulate the PM internally as much as they wish. E.g., an Entity might have "getAddress()" and read from the internal Record (which in turn reads from its internal Row).

Alternatively, we can do "DDD on top of ORM" where repositories map the Records to Entities/Aggregates.

If you have two instances of a particular domain Entity, and you change values on the data Row, it's now reflected across all instances of that particular Entity, because the Row is identity-mapped. Is that a problem?

* * *

How to properly wrap an Atlas record for the Domain?
