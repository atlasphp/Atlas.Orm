# TODO

## Possible Features

- Have Rows force everything to scalar/null? (The Row represents the data as it
  is at the database. It is the Record that might be allowed to do trivial
  modifications for the domain.)

- Add back Record factory?

- Add support for relation-specific joins? E.g.:

        $select = $atlas->select(Mapper::CLASS)
            ->joinWith('foo', 'LEFT', function ($select) {
                $select->joinWith('bar', 'INNER');
            })
            ->where('bar.whatever = 9');

  This gets tricky when a with-with-with has the same name as something else;
  no reasonable way to alias it. Also tricky when the related has the same name
  as an actual table already in the query.

- Add a way to specify self-join table aliases in relationship definitions?

- Add a way to specify custom conditions in relationship definitions?

- Add `addNew()` to RecordSet to append a new Record of the proper type?

- Add support for saving a record and all of its relateds recursively? (Auto-set
  foreign key values. Use a Transaction under the hood.)

## Documentation

- Compare and contrast with:

    - Analogue https://github.com/analogueorm/analogue
    - Doctrine http://www.doctrine-project.org/
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
