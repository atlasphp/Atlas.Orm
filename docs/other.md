# Other Topics

## Adding Custom Mapper Methods

Feel free to add custom methods to your _Mapper_ classes, though do be sure
that they are appropriate to a _Mapper_. For example, custom `fetch*()` methods
are perfectly reasonable, so that you don't have to write the same queries
over and over:

```php
namespace App\DataSource\Content;

use Atlas\Mapper\Mapper;

class Content extends Mapper
{
    public function fetchLatestContent(int $count) : ContentRecordSet
    {
        return $this
            ->select()
            ->orderBy('publish_date DESC')
            ->limit($count)
            ->fetchRecordSet();
    }
}
```

Another example would be custom write behaviors, such as incrementing a value
directly in the database (without going through any events) and modifying the
appropriate _Record_ in memory:

```php
namespace App\DataSource\Content;

use Atlas\Mapper\Mapper;

class Content extends Mapper
{
    public function increment(ContentRecord $record, string $field)
    {
        $this->table
            ->update()
            ->set($field, "{$field} + 1")
            ->where("content_id = ", $record->content_id)
            ->perform();

        $record->$field = $this->table
            ->select($field)
            ->where("content_id = ", $record->content_id)
            ->fetchValue();
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

For example, given a _Content_ mapper and _ContentRecord_ ...

```
App\
    DataSource\
        Content\
            Content.php
            ContentEvents.php
            ContentRecord.php
            ContentRecordSet.php
            ContentRelationships.php
            ContentRow.php
            ContentSelect.php
            ContentTable.php
            ContentTableEvents.php
            ContentTableSelect.php
```

... , you might have the content types of "post", "page", "video", "wiki", and so on.

```
App\
    DataSource\
        Content\
            Content.php
            ContentEvents.php
            ContentRecord.php
            ContentRecordSet.php
            ContentRelationships.php
            ContentRow.php
            ContentSelect.php
            ContentTable.php
            ContentTableEvents.php
            ContentTableSelect.php
            PageContentRecord.php
            PostContentRecord.php
            VideoContentRecord.php
            WikiContentRecord.php
```

A _WikiContentRecord_ might look like this ...

```php
namespace App\DataSource\Content;

class WikiContentRecord extends ContentRecord
{
}
```

... and the _Content_ `getRecordClass()` method would look like this:

```php
namespace App\DataSource\Content;

use Atlas\Mapper\Mapper;
use Atlas\Table\Row;

class Content extends Mapper
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

## Automated Validation

You will probably want to apply some sort of filtering (validation and
sanitizing) to _Row_ (and to a lesser extent _Record_) objects before they get
written back to the database. To do so, implement or override the appropriate
_TableEvents_ (or _MapperEvents_) class methods for `before` or `modify` the
`insert` or `update` event.  Irrecoverable filtering failures should be thrown
as exceptions to be caught by your surrounding application or domain logic.

For example, to check that a value is a valid email address:

```php
namespace App\DataSource\Author;

use Atlas\Table\Row;
use Atlas\Table\Table;
use Atlas\Table\TableEvents;
use UnexpectedValueException;

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
```

For detailed reporting of validation failures, consider writing your own
extended exception class to retain a list of the fields and error messages,
perhaps with the object being validated.

## Query Logging

To enable query logging, call the Atlas `logQueries()` method. Issue your
queries, and then call `getQueries()` to get back the log entries.

```php
// start logging
$atlas->logQueries();

// retrieve connections and issue queries, then:
$queries = $connectionLocator->getQueries();

// stop logging
$connectionLocator->logQueries(false);
```

Each query log entry will be an array with these keys:

- `connection`: the name of the connection used for the query
- `start`: when the query started
- `finish`: when the query finished
- `duration`: how long the query took
- `statement`: the query statement string
- `values`: the array of bound values
- `trace`: an exception trace showing where the query was issued

You may wish to set a custom query logger for Atlas. To do so, call
`setQueryLogger()` and pass a callable with the signature
`function (array $entry) : void`.

```php
class CustomDebugger
{
    public function __invoke(array $entry) : void
    {
        // call an injected logger to record the entry
    }
}

$customDebugger = new CustomDebugger();
$atlas->setQueryLogger($customDebugger);
$atlas->logQueries(true);

// now Atlas will send query log entries to the CustomDebugger
```

> **Note:**
>
> If you set a custom logger, the _Atlas_ instance will no longer retain its own
> query log entries; they will all go to the custom logger. This means that
> `getQueries()` on the _Atlas_ instance will not show any new entries.

## Custom Factory Callable

The _AtlasBuilder_ let you specify a custom factory callable to create the
dependencies for each _Table_ and _Mapper_ instance. The default factory
callable looks like this:

```php
/**
 * @var string $class A fully-qualified class name.
 * @return object
 */
function (string $class) {
    return new $class();
}
```

Although this callable may in future be used for any kind of _Table_ or _Mapper_
dependency, in practice it is currently limited to _Events_ classes.

If your _Events_ instances need dependency injection, you can replace the
default factory with your own callable; the _AtlasBuilder_ will use it to create
any new _Events_ instances.  This gives you full control over how the _Events_
objects are instantiated.

> **Note:**
>
> The base _TableEvents_ and _MapperEvents_ classes have no constructors, so you
> are free to write your own in your generated _Events_ classes.

For example, to use a PSR-11 container to create _Events_ objects:

```php
$atlasBuilder = new \Atlas\Orm\AtlasBuilder(...);

/** @var \Psr\Container\ContainerInterface $container */
$atlasBuilder->setFactory(function (string $class) use ($container) {
    return $container->get($class);
});

$atlas = $atlasBuilder->newAtlas();

// Atlas will now use $container to create
// TableEvents and MapperEvents instances.
```
