# TODO

## Next Release Priority

- Documentation.

- In Table::selectWherePrimary(), wrap entire condition in parens.

## Near-Term

- Reduce the RecordInterface to just what Atlas uses itself: getMapperClass(),
  getRow(), getRelated(). Continue to presume access via properties.

- Does RecordSetInterface currently make sense?

- ??? Add back Record factory?

- ??? Have Rows force everything to scalars, or at least not objects, because
  the Row represents the data as it is at the database. It is the Record that
  might be allowed to do trivial modifications for the domain.

- ??? Support for relation-specific joins. E.g.:

        $select = $atlas->select(Mapper::CLASS)
            ->joinWith('foo', 'LEFT', function ($select) {
                $select->joinWith('bar', 'INNER');
            })
            ->where('bar.whatever = 9');

  This gets tricky when a with-with-with has the same name as something else;
  no reasonable way to alias it. Also tricky when the related has the same name
  as an actual table already in the query.

- ??? Need a way to specify self-join table aliases in relationship definitions?

- ??? Need a way to add custom conditions to relationship definitions?

## Unknown Priority

- Fetching with a to-one related, with a subsquent to-one related, reintroduces
  N+1 at that deeper layer. (Maybe also if with a subsequent to-many related?)

  Need a way to collect the series of native keys and pass into the subsequent
  related, then loop through the single records to stitch in each related
  records.

  Given ...

        Foo oneToMany Bar
            Bar manyToOne Baz
                Baz manyToOne Dib

  ... then this fetch ...

        $atlas->fetchRecordSet('foo')->with([
            'bar' => function ($bar) {
                $bar->with([
                    'baz' => function ($baz) {
                        $baz->with([
                            'dib'
                        ]);
                    },
                ]);
            },
        ]);

  ... goes N+1 when it fetches the 'dib' for each 'baz'. What you would need to
  do is have all 'baz' in hand, get all their 'dib' IDs, fetch all 'dib'

- Add `addNew()` to RecordSet to append a new Record of the proper type.

- Writing back to the database

    - A strategy to save a record and all of its relateds recursively. Auto-set
      foreign key values. Use a Transaction under the hood.

- Docs

    - Compare and contrast with:

        - Analogue
        - Doctrine
        - Flourish
        - Idiorm
        - NotORM
        - Paris
        - Propel
        - RedBean
        - Spot2
        - Zend_Db_Table
        - http://www.gajotres.net/best-available-php-orm-libraries-part-1/
        - http://www.gajotres.net/best-available-php-orm-libraries-part-2/

    - Add examples on how to properly wrap a Record in the Domain.

