# TODO

## Immediate

- Make sure that on() with manyToMany() works as expected.

- Have relationships work through the *Rows* not the *Records*.

- Have Rows force everything to scalars, or at least not objects, because
  the Row represents the data as it is at the database. It is the Record that
  might be allowed to do trivial modifications for the domain.

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
