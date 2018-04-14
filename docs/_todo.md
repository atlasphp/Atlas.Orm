- persisting a RecordSet

- removing relateds; cf. https://github.com/atlasphp/Atlas.Orm/issues/62

- convert field to object and back again, e.g. Date object -- could be a
  be a method on a custom Record

- soft-deletion by marking a field -- method on a custom Record, then have
  the Mapper add "where('soft_deleted = ', false)";

- writing custom Mapper methods

- overriding Row validation (e.g. to allow objects in Rows)

- extending from your own Record, Row, Table, Mapper, Events classes

- Single-table inheritance. Given a `content` table with a `type` column ...

        ```
        <?php
        namespace DataSource\Content;

        use Atlas\Mapper\Record;

        class ContentRecord extends Record {}

        class WikiRecord extends ContentRecord {}

        class BlogRecord extends ContentRecord {}

        class ThreadRecord extends ContentRecord {}

        class ReplyRecord extends ContentRecord {}

        class ContentMapper extends AbstractMapper
        {
            protected function getRecordClass(Row $row)
            {
                // 'wiki' => 'DataSource\Content\WikiRecord'
                return 'DataSource\Content\' . ucfirst($row->type) . 'Record';
            }

            // getRecordSetClass() doesn't work the same, since there might be
            // many different types in there.
        }
        ```
