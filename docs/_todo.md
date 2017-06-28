- removing relateds; cf. https://github.com/atlasphp/Atlas.Orm/issues/62

- convert field to object and back again, e.g. Date object -- should be a
  be a method on a custom Record

- soft-deletion by marking a field -- method on a custom Record

- multiple mappers using a single table

- writing custom mapper methods

- Single-table inheritance. This is stretching things a bit, since STI maps
  a *table* to an *inheritance structure*, which is probably more in the realm
  of domain logic (and a repository system) instead of persistence logic.
  However, given a `content` table with a `type` column ...

        ```
        <?php
        namespace DataSource\Content;

        use Atlas\Orm\Mapper\Record;

        class ContentRecord extends Record {}

        class WikiRecord extends ContentRecord {}

        class BlogRecord extends ContentRecord {}

        class ThreadRecord extends ContentRecord {}

        class ReplyRecord extends ContentRecord {}

        class ContentMapper extends AbstractMapper
        {
            protected function getRecordClass(RowInterface $row)
            {
                // 'wiki' => 'DataSource\Content\WikiRecord'
                return 'DataSource\Content\' . ucfirst($row->type) . 'Record';
            }

            // getRecordSetClass() doesn't work the same, since there might be
            // many different types in there.
        }
        ```

- (I wonder if "polymorphic belongs-to" is also more in the realm of domain
  logic than persistence logic.)

