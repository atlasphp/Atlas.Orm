# Getting Started

## Installation

This package is installable and autoloadable via [Composer](https://getcomposer.org/)
as [atlas/orm](https://packagist.org/packages/atlas/orm). Add the following lines
to your `composer.json` file, then call `composer update`.

```json
{
    "require": {
        "atlas/orm": "~3.0"
    },
    "require-dev": {
        "atlas/cli": "~2.0"
    }
}
```

(The `atlas/cli` package provides the `atlas-skeleton` command-line tool to
help create data-source classes for the mapper system.)

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

Now you can use _Atlas_ to work with your database to fetch and persist _Record_
objects, as well as perform other interactions.
