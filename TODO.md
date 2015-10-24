- Identity Map

    - Consider a single Atlas-wide Identity Map, instead of one per table. Also consider setting in the array values directly instead of getting a copy from the row.

- RowSet/RecordSet

    - Add `append()` or `addNew()` to RowSet and RecordSet to append a new Row/Record of the proper type. Probably need to keep a reference back to the original Table/Mapper for the "new" logic.

- Row

    - Is there any way to use a property-per-column instead of a data array? Make the Identity object private in that case.

    - Also, can we use it to figure out what columns to bring back? Hell, do we even need $cols at the table level when we have $default?

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

    - Single-record strategy to save a record and all of its relateds; probably uses a Unit Of Work under the hood.

- Skeleton

    - Might be convenient to read schema from the database when generating. It's a getting-started tool, not a maintenance tool.

        atlas-skeleton --connection=path/to/connection.php Foo\\DataSource\\Author

- "Meta" or "Schema"

    - Might be convenient to put all data originating from schema into its own file, then extend/use/inject that when needed. Having that might make maintenance based on table changes a lot easier; the skeleton generator could overwrite that file, leaving all others in place.

- Docs

    - Finish them. :-/

    - Add examples on how to properly wrap a Record in the Domain. If you have two instances of a particular domain Entity, and you change values on the data Row, it's now reflected across all instances of that particular Entity, because the Row is identity-mapped. Is that a problem?
