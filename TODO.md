- Row/Record Factories

    - Extract newRow(), newRowSet(), newRecord(), newRecordSet() to factory objects? Would allow for finer user control over how they get created, and might be future-proofing. Does that mean passing the IdentityMap into it?

- Identity Map

    - Consider a single Atlas-wide Identity Map, instead of one per table.

- RowSet/RecordSet

    - Add `append()` or `addNew()` to RowSet and RecordSet to append a new Row/Record of the proper type. Probably need to keep a reference back to the original Table/Mapper for the "new" logic.

- Interfaces

    - Add a RecordInterface (and a RecordSet interface?) so that Domain objects can typehint against the interface, and not necessarily an actual record. Maybe also add a <Type>RecordInterface class that extends RecordInterface, and have <Type>Record implement it.

- Relationships

    - Have each Record and RecordSet note the record it "belongs to" and the foreign key mapping?

    - Then in Record and RecordSet relations, automatically set "belongs to" foriegn key value on `appendNew()`.

- Composite primary keys

    - Build strategies for fetching by composite keys

    - Build strategies for stitching in foregin record with composite keys; consider allowing custom Relation classes for this

- Writing back to the database

    - Unit Of Work that allows you to attach Record objects

    - Single-record strategy to save a record and all of its relateds; probably
    uses a Unit Of Work under the hood.

- Skeleton generator

    Allow for ...

        --table={tablename}
        --primary={primarycol}
        --autoinc={bool}
        --cols="foo,bar,baz"

    ... to specify pertinent values. It also means different templates for different classes.

- Docs

    - Finish them. :-/

    - Add examples on how to properly wrap a Record in the Domain. If you have two instances of a particular domain Entity, and you change values on the data Row, it's now reflected across all instances of that particular Entity, because the Row is identity-mapped. Is that a problem?
