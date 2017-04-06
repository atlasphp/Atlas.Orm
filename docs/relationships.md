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
