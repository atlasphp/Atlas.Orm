- Composite primary keys

    - Build strategies for fetching by composite keys

    - Build strategies for stitching in foregin record with composite keys; consider allowing custom Relation classes for this.

- Row

    - Consider isNew(), isDirty(), isClean(), isDeleted() methods.

    - Consider moving getArrayCopy*() methods back to Row from Table.

- TableTrait

    - retain more column data than just the names: size, scope, etc. This may help users to build basic automated filters at the Row level.

- Generic

    - Consider renaming "Abstract" to "Generic" to indicate what *ought* to be happening, if/when PHP gets generics.

- Command

    - Allow generating only Mapper-related classes, since one Table can support many Mappers. Will need to specify which table class it wraps.

    - Extract to its own Atlas.Cli package, and require-dev it in Atlas.Atlas.

- RowSet/RecordSet/Relations

    - Add `append()` or `addNew()` to RowSet and RecordSet to append a new Row/Record of the proper type. Probably need to keep a reference back to the original Table/Mapper for the "new" logic.

    - Have each Record and RecordSet note the record it "belongs to" and the foreign key mapping?

    - Then in Record and RecordSet relations, automatically set "belongs to" foriegn key value on `appendNew()` ?

    - Alternatively, add a RecordFilter logic to look through relateds and set the foreign key values at insert/update time.

- Related/Relations

    - Add the relationship definitions to the Related object, to support wiring-up of foreign keys?

    - Consider moving away from association terms ('belongs to') back to SQL relationship terms ('many to one').

- Queries

    - Create Table-level insert/update/delete objects, like with select?

    - In MapperSelect, add support for relation-specific joins?

- Writing back to the database

    - Single-record strategy to save a record and all of its relateds recursively; probably uses a Transaction under the hood. Maybe make that the Atlas insert/update/delete methods.

- Docs

    - Finish them. Hell, *start* them. :-/

    - Add examples on how to properly wrap a Record in the Domain.
