# Mapper Relationships

You can add relationships to a mapper inside its `setRelated()` method, calling
one of the four available relationship-definition methods:

- `oneToOne($field, $mapperClass)` (aka "has one")
- `manyToOne($field, $mapperClass)` (aka "belongs to")
- `oneToMany($field, $mapperClass)` (aka "has many")
- `manyToMany($field, $mapperClass, $throughField)` (aka "has many through")

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
        $this->oneToOne('author', AuthorMapper::CLASS)
            ->on([
                // native (threads) column => foreign (authors) column
                'author_id' => 'id',
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

## Custom Relationship SELECT

You may find it useful to add custom conditions, ordering, limits, etc. to
the relationship SELECT object. To do so, call `custom()` on the relationship
and pass a callable with the signature `function ($select)`. This will allow
you to manipulate the foreign SELECT statement for the relationship.

For example, you can handle one side of a so-called polymorphic relationship by
selecting only related records of a particular type. In this case, a `comments`
table has a `commentable` column indicating a record type or table name, and a
`commentable_id` column as the foreign key value.

```php
class IssueMapper extends AbstractMapper
{
    protected function setRelated()
    {
        $this->oneToMany('comments', CommentMapper::CLASS)
            ->on([
                'issue_id' => 'commentable_id'
            ])
            ->custom(function ($select) {
                $select->where('commentable = ?', 'issue');
            });
    }
}
```

The `$select` is fully functional, so you have a great deal of control over
the relationship definition (and the concomitant ability to mess things up).

> N.b.: The `custom()` functionality applies only to foreign record retrieval,
> through the relationship, not to `MapperSelect::joinWith()`.

## Case-Sensitivity

> N.b.: This applies only to **string-based** relationship keys. If you are
> using numeric relationship keys, this section does not apply.

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
