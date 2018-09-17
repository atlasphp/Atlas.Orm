# Getting Started

## Installation

This package is installable and autoloadable via [Composer](https://getcomposer.org/)
as [atlas/orm](https://packagist.org/packages/atlas/orm). Add the following lines
to your `composer.json` file, then call `composer update`.

```json
{
    "require": {
        "atlas/orm": "~2.0"
    },
    "require-dev": {
        "atlas/cli": "~1.0"
    }
}
```

(The `atlas/cli` package provides the `atlas-skeleton` command-line tool to
help create skeleton classes for the mapper.)

## Creating Data Source Classes

You can create your data source classes by hand, but it's going to be tedious to
do so. Instead, use the `atlas-skeleton` command to read the table information
from the database. You can read more about that in the
[atlas/cli docs](https://github.com/atlasphp/Atlas.Cli/blob/1.x/docs/getting-started.md).

## Instantiating Atlas

Create an Atlas instance using the AtlasBuilder.

The builder accepts a [PDO](http://php.net/manual/en/pdo.construct.php),
[ExtendedPdo](https://github.com/auraphp/Aura.Sql/blob/3.x/docs/getting-started.md),
or [ConnectionLocator](https://github.com/auraphp/Aura.Sql/blob/3.x/docs/connection-locator.md)
instance, or you can enter connection parameters and the builder will create a
connection for you.

```php
<?php
$atlasBuilder = new AtlasBuilder(new PDO(...));
// or
$atlasBuilder = new AtlasBuilder(new ExtendedPdo(...));
// or
$atlasBuilder = new AtlasBuilder(new ConnectionLocator(...));
// or
$atlasBuilder = new AtlasBuilder(
    'mysql:host=localhost;dbname=testdb',
    'username',
    'password'
);
```

Then get a new Atlas instance out of the builder:

```php
<?php
$atlas = $atlasBuilder->newAtlas();
```

> **Note:**
>
> Atlas 2.5.x and earlier use the older AtlasContainer to build an Atlas
> object. The older approach requires you to register all mappers with the
> container using its setMappers() method, whereas the newer AtlasBuilder
> in 2.6.x lazy-loads them on demand.
