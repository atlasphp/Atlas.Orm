# TODO

## Immediate

- Look for `throws` and consolidate to Exception factory.

- With fetch*By*(), make sure the array has string keys, not numeric, to avoid "table.0" errors

- Make sure that on() with manyToMany() works as expected.

- Un-temporize Gateway::getAutoinc()

## Next Release Priority

- Documentation.

- Move Plugin up to top, since it handles both Mapper and Gateway?

## Near-Term

- Support for relation-specific joins. E.g.:

        $select = $atlas->select(Mapper::CLASS)
            ->joinWith('foo', 'LEFT', function ($select) {
                $select->joinWith('bar', 'INNER');
            })
            ->where('bar.whatever = 9');

## Unknown Priority

- Add `addNew()` to RecordSet to append a new Record of the proper type.

- Writing back to the database

    - A strategy to save a record and all of its relateds recursively. Auto-set foreign key values. Use a Transaction under the hood.

- Docs

    - Add examples on how to properly wrap a Record in the Domain.
