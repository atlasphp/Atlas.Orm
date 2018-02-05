# Mapper Relationships

You can add relationships to a mapper inside its `setRelated()` method, calling
one of the five available relationship-definition methods:

- `oneToOne($field, $mapperClass)` (aka "has one")
- `manyToOne($field, $mapperClass)` (aka "belongs to")
- `oneToMany($field, $mapperClass)` (aka "has many")
- `manyToMany($field, $mapperClass, $throughField)` (aka "has many through")
- `manyToOneByReference($field, $referenceCol)` (aka "polymorphic association")

The `$field` will become a field name on the returned Record object. That field
will be populated from the specified `$mapperClass` in Atlas. (In the case of
`manyToMany()`, the association mappings will come from the specified
`$throughField`.)

Here is an example:

```php
<?php
namespace App\DataSource\Thread;

use App\DataSource\Author\AuthorMapper;
use App\DataSource\Summary\SummaryMapper;
use App\DataSource\Reply\ReplyMapper;
use App\DataSource\Tagging\TaggingMapper;
use App\DataSource\Tag\TagMapper;
use Atlas\Orm\Mapper\AbstractMapper;

class ThreadMapper extends AbstractMapper
{
    protected function setRelated()
    {
        $this->manyToOne('author', AuthorMapper::CLASS);
        $this->oneToOne('summary', SummaryMapper::CLASS);
        $this->oneToMany('replies', ReplyMapper::CLASS);
        $this->oneToMany('taggings', TaggingMapper::CLASS);
        $this->manyToMany('tags', TagMapper::CLASS, 'taggings');
    }
}
```

## Relationship Key Columns

By default, in all relationships except many-to-one, the relationship will take
the primary key column(s) in the native table, and map to those same column
names in the foreign table.

In the case of many-to-one, it is the reverse; that is, the relationship will
take the primary key column(s) in the foreign table, and map to those same
column names in the native table.

If you want to use different columns, call the `on()` method on the
relationship. For example, if the threads table uses `author_id`, but the
authors table uses just `id`, you can do this:

```php
<?php
class ThreadMapper extends AbstractMapper
{
    protected function setRelated()
    {
        $this->manyToOne('author', AuthorMapper::CLASS)
            ->on([
                // native (threads) column => foreign (authors) column
                'author_id' => 'id',
            ]);
        // ...
    }
}
```
And on the `oneToMany` side of the relationship, you use the native author table
`id` column with the foreign threads table `author_id` column.
```php
<?php
class AuthorMapper extends AbstractMapper
{
    protected function setRelated()
    {
        $this->oneToMany('threads', ThreadMapper::CLASS)
            ->on([
                // native (author) column => foreign (threads) column
                'id' => 'author_id',
            ]);
        // ...
    }
}
```

## Composite Relationship Keys

Likewise, if a table uses a composite key, you can re-map the relationship on
multiple columns. If table `foo` has composite primary key columns of `acol` and
`bcol`, and it maps to table `bar` on `foo_acol` and `foo_bcol`, you would do
this:

```php
<?php
class FooMapper
{
    protected function setRelated()
    {
        $this->oneToMany('bars', BarMapper::CLASS)
            ->on([
                // native (foo) column => foreign (bar) column
                'acol' => 'foo_acol',
                'bcol' => 'foo_bcol',
            ]);
    }
}
```

## Case-Sensitivity

> **Note:**
  This applies only to **string-based** relationship keys. If you are
  using numeric relationship keys, this section does not apply.

Atlas will match records related by string keys in a case-senstive manner. If
your collations on the related string key columns are *not* case sensitive,
Atlas might not match up related records properly in memory after fetching them
from the database. This is because 'foo' and 'FOO' might be equivalent in the
database collation, but they are *not* equivalent in PHP.

In that kind of situation, you will want to tell the relationship to ignore the
case of related string key columns when matching related records. You can do so
with the `ignoreCase()` method on the relationship definition.

```php

<?php
class FooMapper
{
    protected function setRelated()
    {
        $this->oneToMany('bars', BarMapper::CLASS)
            ->ignoreCase();
    }
}
```

With that in place, a native value of 'foo' match to a foreign value of 'FOO'
when Atlas is stitching together related records.

## Simple WHERE Conditions

You may find it useful to define simple WHERE conditions on the foreign side of
the relationship. For example, you can handle one side of a many-to-one
relationship by reference (aka "polymorphic association") by selecting only
related records of a particular type.

In the following example, a `comments` table has a `commentable_id` column as
the foreign key value, but is restricted to "video" values on a discriminator
column named `commentable_type`.

```php
class IssueMapper extends AbstractMapper
{
    protected function setRelated()
    {
        $this->oneToMany('comments', CommentMapper::CLASS)
            ->on([
                'video_id' => 'commentable_id'
            ])
            ->where('commentable_type = ?', 'video');
    }
}
```

(These conditions will be honored by `MapperSelect::*joinWith()` as well.)

## Relationships By Reference

The many-to-one relationship by reference is somewhat different from the other
relationship types. It is identical to a many-to-one relationship, except that
the relationships vary by a reference column in the native table. This allows
rows in the native table to "belong to" rows in more than one foreign table. The
typical example is one of comments that can be created on many different kinds
of content, such as static pages, blog posts, and video links.

```php
class CommentMapper extends AbstractMapper
{
    protected function setRelated()
    {
        // The first argument is the field name on the native record;
        // the second argument is the reference column on the native table.
        $this->manyToOneByReference('commentable', 'commentable_type')

            // The first argument is the value of the commentable_type column;
            // the second is the related foreign mapper class;
            // the third is the native-to-foreign column mapping.
            ->to('page', PageMapper::CLASS, ['commentable_id' => 'page_id'])
            ->to('post', PostMapper::CLASS, ['commentable_id' => 'post_id'])
            ->to('video', VideoMapper::CLASS, ['commentable_id' => 'video_id']);
    }
}
```

Note that there will be one query per type of reference in the native record
set. That is, if a native record set (of an arbitrary number of records) refers
to a total of three different relationships, then Atlas will issue three
additional queries to fetch the related records.

The phrase "relationship by reference" is used here instead of "polymorphic
association" because the latter is an OOP term, not an SQL term. The former is
more SQL-ish, and is lifted from Postgres; cf.
<https://www.postgresql.org/docs/9.4/static/sql-createtable.html> and search for
"REFERENCES".
