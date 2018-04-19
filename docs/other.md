# Other Topics

## Adding Custom Mapper Methods

Feel free to add custom methods to your _Mapper_ classes, though do be sure
that they are appropriate to a _Mapper_. For example, custom `fetch*()` methods
are perfectly reasonable, so that you don't have to write the same queries
over and over:

```php
<?php
namespace App\DataSource\Content;

use Atlas\Mapper\Mapper;

class ContentMapper extends Mapper
{
    public function fetchLatestContent(int $count)
    {
        return $this
            ->select()
            ->orderBy('publish_date DESC')
            ->limit($count)
            ->fetchRecordSet();
    }
}
```

## Single Table Inheritance

Sometimes you will want to use one _Mapper_ (and its underlying _Table_) to
create more than one kind of _Record_. The _Record_ type is generally specified
by a column on the table, e.g. `record_type`. To do so, create _Record_ classes
that extend the _Record_ for that _Mapper_ in the same namespace as the
_Mapper_, then override the _Mapper_ `getRecordClass()` method to return the
appropriate class name.

For example, given a _ContentMapper_ and _ContentRecord_ ...

```
App\
    DataSource\
        Content\
            ContentMapper.php
            ContentMapperEvents.php
            ContentMapperRelationships.php
            ContentRecord.php
            ContentRecordSet.php
            ContentRow.php
            ContentTable.php
            ContentTableEvents.php
```

... , you might have the content types of "post", "page", "video", "wiki", and so on.

```
App\
    DataSource\
        Content\
            ContentMapper.php
            ContentMapperEvents.php
            ContentMapperRelationships.php
            ContentRecord.php
            ContentRecordSet.php
            ContentRow.php
            ContentTable.php
            ContentTableEvents.php
            PageContentRecord.php
            PostContentRecord.php
            VideoContentRecord.php
            WikiContentRecord.php
```

A _WikiContentRecord_ might look like this ...

```php
<?php
namespace App\DataSource\Content;

class WikiContentRecord extends ContentRecord
{
}
```

... and the _ContentMapper_ `getRecordClass()` method would look like this:

```php
<?php
namespace App\DataSource\Content;

use Atlas\Mapper\Mapper;
use Atlas\Table\Row;

class ContentMapper extends Mapper
{
    protected function getRecordClass(Row $row) : Record
    {
        switch ($row->type) {
            case 'page':
                return PageContentRecord::CLASS;
            case 'post':
                return PostContentRecord::CLASS;
            case 'video':
                return VideoContentRecord::CLASS;
            case 'Wiki':
                return PostContentRecord::CLASS;
            default:
                return ContentRecord::CLASS:
        }
    }
}
```

Note that you cannot define different relationships "per record."  You can only
define _MapperRelationships_ for the mapper as whole, to cover all its record
types.

Note also that there can only be one _RecordSet_ class per _Mapper_, though it
can contain any kind of _Record_.

## Managing Many-To-Many Relateds

Given the typical example of a `blog` table, associated to `tags`, through a
`taggings` table, here is how you would add a tag to a blog post:

```php
<?php
// get a blog post, with taggings and tags
$blog = $atlas->fetchRecord(BlogMapper::CLASS, $blog_id, [
    'taggings' => [
        'tags'
    ]
]);

// get all tags in the system
$tags = $atlas->fetchRecordSet(TagsMapper::CLASS);

// create the new tagging association, with the related blog and tag objects
$tagging = $thread->taggings->appendNew([
    'blog' => $blog,
    'tag' => $tags->getOneBy(['name' => $tag_name])
]);

// persist the whole blog record, which will insert the tagging
$atlas->persist($tagging);
```

Similarly, here is how you would remove a tag:

```
// mark the Tagging association for deletion
$blog->taggings
    ->getOneBy(['name' => $tag_name)
    ->setDelete();

// persist the whole record with the tagging relateds,
// which will delete the tagging and detach the related record
$atlas->persist($thread);
```

## Automated Validation

You will probably want to apply some sort of filtering (validation and
sanitizing) to _Row_ (and to a lesser extent _Record_) objects before they get
written back to the database. To do so, implment or override the appropriate
_TableEvents_ (or _MapperEvents_) class methods for `before` or `modify` the
`insert` or `update` event.  Irrecoverable filtering failures should be thrown
as exceptions to be caught by your surrounding application or domain logic.

For example, to check that a value is a valid email address:

<?php
namespace App\DataSource\Author;

use Atlas\Table\Row;
use Atlas\Table\Table;
use Atlas\Table\TableEvents;
use U
class AuthorTableEvents extends TableEvents
{
    public function beforeInsert(Table $table, Row $row) : void
    {
        $this->assertValidEmail($row->email);
    }

    public function beforeUpdate(Table $table, Row $row) : void
    {
        $this->assertValidEmail($row->email);
    }

    protected function assertValidEmail($value)
    {
        if (! filter_var($value, FILTER_VALIDATE_EMAIL) {
            throw new UnexpectedValueException("The author email address is not valid.");
        }
    }
}

For extended reporting of validation failures, consider writing your own
extended exception class to retain a list of the fields and error messages,
perhaps with the object being validated.

## Dependency Injection

_Atlas_ makes an allowance for dependency injection in only two places: the
_MapperEvents_ class and the _TableEvents_ class. All other classes in _Atlas_
claim the constructor for themselves.

To inject dependencies into one of the events classes, create a general-purpose
callable factory to create and return the requested class:
