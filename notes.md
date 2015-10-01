
RowSet/RecordSet:

    Add "append()" or "addNew()" to Sets to append a new Row/Record of the proper type. Probably need to keep a reference back to the original Table/Mapper for the "new" logic. Alternatively, extract RowFactory and RecordFactory for use in the Sets. Hm, but that entails dealing with the IdentityMap as well.

Record:

    Add a RecordInterface (and a RecordSet interface?) so that Domain objects can typehint against the interface, and not necessarily an actual record. Maybe also add a <Type>RecordInterface class that extends RecordInterface, and have <Type>Record implement it.

Relationships:

    More tests for when relationships are missing.

    The relationships may be reusing record objects, rather than building new ones. Is that a problem?

    Have each Record and RecordSet note the record it "belongs to" and the foreign key mapping.

    Then in Record and RecordSet relations, automatically set "belongs to" foriegn key value on appendNew().


Compound primary keys:

    Allow for compound primaries.

    Build fetching strategies and identity lookups for compound keys.

* * *

What we're going for is "Domain Model composed of Persistence Model". That is, the Domain entities/aggregates use Records and RecordSets internally, but never expose them. They can manipulate the PM internally as much as they wish. E.g., an CustomerEntity might have "getAddress()" and read from the internal CustomerRecord. Alternatively, we can do "DDD on top of ORM" where repositories map the Records to Entities/Aggregates.

Now, when you change the values on the Entity: if you have two instances of a particular CustomerEntity, and you change values on the CustomerRow, it's now reflected across all instances of that particular CustomerEntity, because the CustomerRow is identity-mapped. Is that a problem?

* * *

In generator, allow for:

    --table={tablename}
    --primary={primarycol}
    --autoinc={bool}

That will allow specification of pertinent values. It also means different templates for different classes.

Also allow for `--dir={dir}` so you don't need to `cd` into the right directory.

* * *

How to properly wrap an Atlas record for the Domain?
