# Getting Started

If you are using Symfony 4, you can get started by installing the [Atlas.Symfony](https://github.com/atlasphp/Atlas.Symfony) bundle.

If you are using Slim 3, please see the [cookbook recipe for Atlas](https://www.slimframework.com/docs/v3/cookbook/database-atlas.html).

Otherwise, read below for the stock installation instructions.

## Installation

This package is installable and autoloadable via [Composer](https://getcomposer.org/)
as [atlas/orm](https://packagist.org/packages/atlas/orm). Add the following lines
to your `composer.json` file, then call `composer update`.

```json
{
    "require": {
        "atlas/orm": "~3.0",
    },
    "require-dev": {
        "atlas/cli": "~2.0"
    }
}
```

(The `atlas/cli` package provides the `atlas-skeleton` command-line tool to
help create data-source classes for the mapper system.)

> **Note:**
>
> If you are using PHPStorm, you may wish to copy the IDE meta file to your
> project to get full autocompletion on Atlas classes:
>
> ```
> cp ./vendor/atlas/orm/resources/phpstorm.meta.php ./.phpstorm.meta.php
> ```

## Skeleton Generation

Next, you will need to create the prerequsite data-source classes using
[Atlas.Cli 2.x](/cassini/skeleton/usage.html).

## Instantiating Atlas

Now you can create an _Atlas_ instance by using its static `new()` method and
passing your PDO connection parameters:

```php
use Atlas\Orm\Atlas;

$atlas = Atlas::new(
    'mysql:host=localhost;dbname=testdb',
    'username',
    'password'
);
```

Optionally, you may pass a _Transaction_ class name as the final parameter.
(By default, _Atlas_ will use an _AutoCommit_ strategy, where transactions have
to be managed manually.)

```php
use Atlas\Orm\Atlas;
use Atlas\Orm\Transaction\AutoTransact;

$atlas = Atlas::new(
    'mysql:host=localhost;dbname=testdb',
    'username',
    'password',
    AutoTransact::CLASS
);
```

Alternatively, use the _AtlasBuilder_ if you need to define a custom factory
callable, such as for _TableEvents_ and _MapperEvents_ classes.

```php
use Atlas\Orm\AtlasBuilder;
use Atlas\Orm\Transaction\BeginOnRead;

$builder = new AtlasBuilder(
    'mysql:host=localhost;dbname=testdb',
    'username',
    'password'
);

// get the ConnectionLocator to set read and write connection factories
$builder->getConnectionLocator()->...;

// set a Transaction class (the default is AutoCommit)
$builder->setTransactionClass(BeginOnRead::CLASS);

// set a custom factory callable
$builder->setFactory(function ($class) {
    return new $class();
});

// get a new Atlas instance
$atlas = $builder->newAtlas();
```

## Next Steps

Now you can use _Atlas_ to work with your database to fetch and persist _Record_
objects, as well as perform other interactions.

- [Define relationships between mappers](./relationships.md)

- [Fetch Records and RecordSets](./reading.md)

- Work with [Records](./records.md) and [RecordSets](./record-sets.md)

- [Manage transactions](./transactions.md)

- [Add Record and RecordSet behaviors](./behavior.md)

- [Handle events](./events.md)

- [Perform direct lower-level queries](./direct.md)

- [Other topics](./other.md) such as custom mapper methods, single table inheritance, many-to-many relationships, and automated validation
