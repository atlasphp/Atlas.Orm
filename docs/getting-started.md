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

## Instantiating Atlas

First, you will need to create the prerequsite data-source classes using
[Atlas.Cli 2.x](https://github.com/atlasphp/Atlas.Cli).

Then, you can create an _Atlas_ instance by using its static `new()` method and
passing your PDO connection parameters:

```php
<?php
use Atlas\Orm\Atlas;

$atlas = Atlas::new(
    'mysql:host=localhost;dbname=testdb',
    'username',
    'password'
);
```

Optionally, you may pass a _Transaction_ class name as the final parameter.
(By default, _Atlas_ will use a _MiniTransaction_ instance.)

```php
<?php
use Atlas\Orm\Atlas;
use Atlas\Orm\LongTransaction;

$atlas = Atlas::new(
    'mysql:host=localhost;dbname=testdb',
    'username',
    'password',
    LongTransaction::CLASS
);
```

Alternatively, use the _AtlasBuilder_ if you need to define a custom factory
callable, such as for _TableEvents_ and _MapperEvents_ classes.

```php
<?php
use Atlas\Orm\AtlasBuilder;
use Atlas\Orm\LongTransaction;

$builder = new AtlasBuilder(
    'mysql:host=localhost;dbname=testdb',
    'username',
    'password'
);

// get the ConnectionLocator to set read and write connection factories
$builder->getConnectionLocator()->...;

// set a Transaction class (the default is MiniTransaction)
$builder->setTransactionClass(LongTransaction::CLASS);

// set a custom factory callable
$builder->setFactory(function ($class) {
    return new $class();
});

// get a new Atlas instance
$atlas = $builder->newAtlas();
```

Now you can use _Atlas_ to work with your database to fetch and persist _Record_
objects, as well as perform other interactions.
