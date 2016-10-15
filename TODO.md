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

- Add support for saving a record and all of its relateds recursively? (Auto-set
  foreign key values. Use a Transaction under the hood.)

- Consider adding a way to put all reads *and* writes inside a transaction, as
  per <https://blog.acolyer.org/2015/09/04/feral-concurrency-control-an-empirical-investigation-of-modern-application-integrity/>

## Documentation

- Compare and contrast with:

    - (Via <http://www.gajotres.net/best-available-php-orm-libraries-part-1/>
      and <http://www.gajotres.net/best-available-php-orm-libraries-part-2/>)

    - Data Mappers

        - Analogue
            - Data Mapper, Domain
            - https://github.com/analogueorm/analogue
        - Doctrine 2
            - Data Mapper, Domain (?)
            - http://www.doctrine-project.org/
        - RedBean 4
            - Data Mapper
            - http://redbeanphp.com/
        - Spot2
            - Data Mapper
            - http://phpdatamapper.com/

    - Not Data Mappers:

        - Flourish
            - Active Record
            - http://flourishlib.com/docs/fActiveRecord
            - http://flourishlib.com/docs/fRecordSet
        - Idiorm & Paris
            - Active Record
            - http://j4mie.github.io/idiormandparis/
        - NotORM
            - ???
            - http://www.notorm.com/
        - Propel
            - Active Record
            - http://propelorm.org/
        - Zend_Db_Table
            - Table Data Gateway
            - https://docs.zendframework.com/zend-db/table-gateway/

- Add examples on how to wrap a Record in the Domain.

    - Include note that Repos+UOW are not a good idea?
      <http://rob.conery.io/2014/03/04/repositories-and-unitofwork-are-not-a-good-idea/>

- How to ...

    - "Trivially export a record to JSON" per <https://twitter.com/taylorotwell/status/652535241765089280> -- `json_encode($record)`

    - increment/decrement a Record field -- via events, and select back the new
      count?

    - convert field to object and back again, e.g. Date object -- should be a
      be a method on a custom Record

    - update *other* records on insert/update/delete; e.g. trees/lists/etc --
      has to be part of events?

    - auto-set field on insert/update, e.g. created_on, updated_on -- best to
      be part of events

    - soft-deletion by marking a field -- method on a custom Record

    - check the database for presence/nonpresence of values (uniqueness) -- part
      of validation, so part of events

    - single-table inheritance -- already there with Mapper::getRecordClass() ?

    - sanitize/validate Records and Rows -- as events

    - Manage many-to-many relationships, e.g. tags through taggings.

        - Adding a tag:

            ```
            // get from a pre-fetched RecordSet of tags
            $tag = $tags->getOneBy(['name' => $tag_name]);

            // add Tag to in-memory Record
            $post->tags[] = $tag;

            // create new Tagging in memory and set columns on row
            $tagging = $post->taggings->appendNew([
                'post_id' => $post->id,
                'tag_id' => $tag->id
            ]);

            // plan to insert the new Tagging
            $transaction->insert($tagging);
            ```

        - Removing a tag

            ```
            // remove from in-memory RecordSet
            $tag = $post->tags->removeOneBy(['name' => $tag_name]);

            // remove from in-memory RecordSet
            $tagging = $post->taggings->removeOneBy(['tag_id' => $tag->id);

            // plan to delete the Tagging
            $transaction->delete($tagging);
            ```
