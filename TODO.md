- RowSet/RecordSet/Relations

    - Add `append()` or `addNew()` to RowSet and RecordSet to append a new Row/Record of the proper type. Probably need to keep a reference back to the original Table/Mapper for the "new" logic.

    - Have each Record and RecordSet note the record it "belongs to" and the foreign key mapping?

    - Then in Record and RecordSet relations, automatically set "belongs to" foriegn key value on `appendNew()` ?

- Related/Relations

    - Add the relationship definitions to the Related object, to support wiring-up of foreign keys?

- Queries

    - Create Table-level insert/update/delete objects, like with select?

- Composite primary keys

    - Build strategies for fetching by composite keys

    - Build strategies for stitching in foregin record with composite keys; consider allowing custom Relation classes for this

- Writing back to the database

    - Unit Of Work that allows you to attach Record objects; should also allow you to attach Query objects for set-related updates/deletes/etc.

    - Single-record strategy to save a record and all of its relateds; probably uses a Unit Of Work under the hood.

- Skeleton

    - Allow generating only Mapper-related classes, since one Table can support many Mappers.

    - Add @property dcoblock elements for code-completion in Row and Record.

- Table(Schema|Info)

    - Might be convenient to put all data originating from schema into its own class or trait, then extend/inject/use it when needed. Having that might make maintenance based on table changes a lot easier; the skeleton generator could always safely overwrite that file. This means only $primary, $autoinc, $cols, and possibly $default. The class

- Docs

    - Finish them. Hell, *start* them. :-/

    - Add examples on how to properly wrap a Record in the Domain.

    - If you have two instances of a particular domain Entity, and you change values on the data Row, it's now reflected across all instances of that particular Entity, because the Row is identity-mapped. Is that a problem?
