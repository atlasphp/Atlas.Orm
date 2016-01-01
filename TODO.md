# TODO

## Next Release Priority

- Documentation.

- Move Plugin up to top, since it handles both Mapper and Gateway?

## Near-Term

- Support for relation-specific joins. E.g.:

        $select = $atlas->select(Mapper::CLASS)
            ->leftJoinWith('foo')
            ->innerJoinWith('bar')
            ->joinWith('OUTER', 'baz');

## Unknown Priority

- Add `addNew()` to RecordSet to append a new Record of the proper type.

- Composite primary keys

    - Build strategies for fetching by composite keys.

    - Build strategies for stitching in foreign record with composite keys; consider allowing custom Relation classes for this.

- Writing back to the database

    - A strategy to save a record and all of its relateds recursively. Auto-set foreign key values. Use a Transaction under the hood.

- Docs

    - Add examples on how to properly wrap a Record in the Domain.
