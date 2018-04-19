# Events

There are several events that will automatically be called when interacting with a
Atlas objects.


## Table Events

There are several events that will automatically be called when interacting with a
Table object. If you used the Atlas CLI tool with the `--full` option, a
TableEvents class will be created for you. For example, `ThreadTableEvents.php`.
With this class, you can override any of the available mapper events.

The `insert()`, `update()`, and `delete()` methods all have 3 events associated
with them: a `before*()`, a `modify*()`, and an `after*()`. In addition, there
is a `modifySelect()` event, and a `modifySelectedRow()` event.

```php
<?php
// Runs after the Select object is created, but before it is executed
modifySelect(Table $table, TableSelect $select)

// Runs after a newly-selected row is instantiated, but before it is
// identity-mapped.
modifySelectedRow(Table $table, Row $row)

// Runs before the Insert object is created
beforeInsert(Table $table, Row $row)

// Runs after the Insert object is created, but before it is executed
modifyInsert(Table $table, Row $row, Insert $insert)

// Runs after the Insert object is executed
afterInsert(Table $table,
            Row $row,
            Insert $insert,
            PDOStatement $pdoStatement)

// Runs before the Update object is created
beforeUpdate(Table $table, Row $row)

// Runs after the Update object is created, but before it is executed
modifyUpdate(Table $table, Row $row, Update $update)

// Runs after the Update object is executed
afterUpdate(Table $table,
            Row $row,
            Update $update,
            PDOStatement $pdoStatement)

// Runs before the Delete object is created
beforeDelete(Table $table, Row $row)

// Runs after the Delete object is created, but before it is executed
modifyDelete(Table $table, Row $row, Delete $delete)

// Runs after the Delete object is executed
afterDelete(Table $table,
            Row $row,
            Delete $delete,
            PDOStatement $pdoStatement)
```

These would be the place to put behaviors such as setting `inserted_at` or
`updated_at` values, etc:

```php
<?php
namespace Blog\DataSource\Posts;

use Atlas\Table\MapperEvents;
use Atlas\Table\Table;
use Atlas\Table\Row;

/**
 * @inheritdoc
 */
class PostsTableEvents extends TableEvents
{
    public function beforeUpdate(Table $table, Row $row)
    {
        $row->inserted_at = date('Y-m-d H:i:s');
    }

    public function beforeUpdate(Table $table, Row $row)
    {
        $row->updated_at = date('Y-m-d H:i:s');
    }
}
```

## Mapper Events

The `insert()`, `update()`, and `delete()` methods all have 3 events associated
with them: a `before*()`, a `modify*()`, and an `after*()`. In addition, there
is a `modifySelect()` event.

```php
<?php
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
<?php
namespace Blog\DataSource\Posts;

use Atlas\Mapper\MapperEvents;
use Atlas\Mapper\Mapper;
use Atlas\Mapper\Record;

/**
 * @inheritdoc
 */
class PostsMapperEvents extends MapperEvents
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
<?php
try {
    $atlas->update($post);
} catch (\Exception $e) {
    foreach ($post->getErrors() as $error) {
        echo $error . '<br/>';
    }
}
```
