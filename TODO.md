# TODO

## Initial Release Priority

- Deal with abstracts.

- Add `append()` or `addNew()` to RowSet and RecordSet to append a new Row/Record of the proper type.

- Documentation.

## Near-Term

- In MapperSelect, add support for relation-specific joins

- Add Row::isNew(), isDirty(), isClean(), isDeleted() methods. Will need setStatus()/getStatus() as well. The idea is that it will help with auto-saving strategies on Record objects.

## Unknown Priority

- Composite primary keys

    - Build strategies for fetching by composite keys

    - Build strategies for stitching in foreign record with composite keys; consider allowing custom Relation classes for this.

- Command

    - Allow generating only Mapper-related classes, since one Table can support many Mappers. Will need to specify which table class it wraps.

- Auto-Managing Related Records

    - Add the relationship definitions to the Related object, to support wiring-up of foreign keys?

    - Add a RecordFilter logic to look through relateds and set the foreign key values at insert/update time? This should probably be as methods on each Relation type.

- Writing back to the database

    - Single-record strategy to save a record and all of its relateds recursively; probably uses a Transaction under the hood.

    - Adding a Record to a RecordSet marks it for insert, but removing a record does not mark it for deletion. How to do so?

- Docs

    - Add examples on how to properly wrap a Record in the Domain.
