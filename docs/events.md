# Events

There are several events that will automatically be called when interacting with
Atlas mappers.

> **Note**:
>
> These mapper-level events are called in addition to the various
> [table-level events](/cassini/table/events.html).

The `insert()`, `update()`, and `delete()` methods all have 3 events associated
with them: a `before*()`, a `modify*()`, and an `after*()`. In addition, there
is a `modifySelect()` event.

```php
// Runs after the Select object is created, but before it is executed
modifySelect(Mapper $mapper, MapperSelect $select)

// Runs before the Insert object is created
beforeInsert(Mapper $mapper, Record $record)

// Runs after the Insert object is created, but before it is executed
modifyInsert(Mapper $mapper, Record $record, Insert $insert)

// Runs after the Insert object is executed
afterInsert(Mapper $mapper,
            Record $record,
            Insert $insert,
            PDOStatement $pdoStatement)

// Runs before the Update object is created
beforeUpdate(Mapper $mapper, Record $record)

// Runs after the Update object is created, but before it is executed
modifyUpdate(Mapper $mapper, Record $record, Update $update)

// Runs after the Update object is executed
afterUpdate(Mapper $mapper,
            Record $record,
            Update $update,
            PDOStatement $pdoStatement)

// Runs before the Delete object is created
beforeDelete(Mapper $mapper, Record $record)

// Runs after the Delete object is created, but before it is executed
modifyDelete(Mapper $mapper, Record $record, Delete $delete)

// Runs after the Delete object is executed
afterDelete(Mapper $mapper,
            Record $record,
            Delete $delete,
            PDOStatement $pdoStatement)
```

Here is a simple example with the assumption that the Record object has a
`validate()` method and a `getErrors()` method. See the section on [Adding Logic
to Records and RecordSets](behavior.html).

```php
namespace Blog\DataSource\Posts;

use Atlas\Mapper\Mapper;
use Atlas\Mapper\MapperEvents;
use Atlas\Mapper\Record;

/**
 * @inheritdoc
 */
class PostsEvents extends MapperEvents
{
    public function beforeUpdate(Mapper $mapper, Record $record)
    {
        if (! $record->validate())
            throw new \Exception('Update Error');
        }

    }
}
```

And you might have something like this in your code:

```php
try {
    $atlas->update($post);
} catch (\Exception $e) {
    foreach ($post->getErrors() as $error) {
        echo $error . '<br/>';
    }
}
```
