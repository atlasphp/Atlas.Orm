# Events

## Mapper Events

There are several events that will automatically be called when interacting with a
Mapper object. If you used the Atlas CLI tool with the `--full` option, a
MapperEvents class will be created for you. For example, `ThreadMapperEvents.php`.
With this class, you can override any of the available mapper events.

The `insert()`, `update()`, and `delete()` methods all have 3 events associated
with them: a `before*()`, a `modify*()`, and an `after*()`. In addition, there
is a `modifySelect()` event.

```php
<?php
// Runs after the Select object is created, but before it is executed
modifySelect(MapperInterface $mapper, MapperSelect $select)

// Runs before the Insert object is created
beforeInsert(MapperInterface $mapper, RecordInterface $record)

// Runs after the Insert object is created, but before it is executed
modifyInsert(MapperInterface $mapper, RecordInterface $record, Insert $insert)

// Runs after the Insert object is executed
afterInsert(MapperInterface $mapper,
            RecordInterface $record,
            Insert $insert,
            PDOStatement $pdoStatement)

// Runs before the Update object is created
beforeUpdate(MapperInterface $mapper, RecordInterface $record)

// Runs after the Update object is created, but before it is executed
modifyUpdate(MapperInterface $mapper, RecordInterface $record, Update $update)

// Runs after the Update object is executed
afterUpdate(MapperInterface $mapper,
            RecordInterface $record,
            Update $update,
            PDOStatement $pdoStatement)

// Runs before the Delete object is created
beforeDelete(MapperInterface $mapper, RecordInterface $record)

// Runs after the Delete object is created, but before it is executed
modifyDelete(MapperInterface $mapper, RecordInterface $record, Delete $delete)

// Runs after the Delete object is executed
afterDelete(MapperInterface $mapper,
            RecordInterface $record,
            Delete $delete,
            PDOStatement $pdoStatement)
```

Here is a simple example with the assumption that the Record object has a
`validate()` method and a `getErrors()` method. See the section on [Adding Logic
to Records and RecordSets](behavior.html).

```php
<?php
namespace Blog\DataSource\Posts;

use Atlas\Orm\Mapper\MapperEvents;
use Atlas\Orm\Mapper\MapperInterface;
use Atlas\Orm\Mapper\RecordInterface;
use Atlas\Orm\Exception;

/**
 * @inheritdoc
 */
class PostsMapperEvents extends MapperEvents
{
    public function beforeUpdate(MapperInterface $mapper, RecordInterface $record)
    {
        if (! $record->validate())
            throw new Exception('Update Error');
        }

    }
}
```

And you might have something like this in your code:

```php
<?php
$success = $atlas->update($post);
if ($sucess) {
    echo "Post updated";
} else {
    foreach ($post->getErrors as $error) {
        echo $error . '<br/>';
    }
}
```

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
modifySelect(TableInterface $table, TableSelect $select)

// Runs after a newly-selected row is instantiated, but before it is
// identity-mapped.
modifySelectedRow(TableInterface $table, Row $row)

// Runs before the Insert object is created
beforeInsert(TableInterface $table, Row $row)

// Runs after the Insert object is created, but before it is executed
modifyInsert(TableInterface $table, Row $row, Insert $insert)

// Runs after the Insert object is executed
afterInsert(TableInterface $table,
            Row $row,
            Insert $insert,
            PDOStatement $pdoStatement)

// Runs before the Update object is created
beforeUpdate(TableInterface $table, Row $row)

// Runs after the Update object is created, but before it is executed
modifyUpdate(TableInterface $table, Row $row, Update $update)

// Runs after the Update object is executed
afterUpdate(TableInterface $table,
            Row $row,
            Update $update,
            PDOStatement $pdoStatement)

// Runs before the Delete object is created
beforeDelete(TableInterface $table, Row $row)

// Runs after the Delete object is created, but before it is executed
modifyDelete(TableInterface $table, Row $row, Delete $delete)

// Runs after the Delete object is executed
afterDelete(TableInterface $table,
            Row $row,
            Delete $delete,
            PDOStatement $pdoStatement)
```

If the `beforeInsert()` method returns an array, that array will be used for the
insert data; otherwise, `Row::getArrayCopy()` will be used.

If the `beforeUpdate()` method returns an array, that array will be used for the
update data; otherwise, `Row::getArrayDiff()` will be used.
