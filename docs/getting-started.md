# Getting Started

## Installation

This package is installable and autoloadable via [Composer](https://getcomposer.org/)
as [atlas/orm](https://packagist.org/packages/atlas/orm). Add the following lines
to your `composer.json` file, then call `composer update`.

```json
{
    "require": {
        "atlas/orm": "~1.0"
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
[atlas/cli docs](https://github.com/atlasphp/Atlas.Cli/blob/1.x/README.md).

## Instantiating Atlas

Create an Atlas instance using the AtlasContainer, passing along the default
ExtendedPdo connection parameters:

```php
<?php
$atlasContainer = new AtlasContainer(
    'mysql:host=localhost;dbname=testdb',
    'username',
    'password'
);
```

Next, set the available mapper classes into the container.

```php
<?php
$atlasContainer->setMappers([
    AuthorMapper::CLASS,
    ReplyMapper::CLASS,
    SummaryMapper::CLASS,
    TagMapper::CLASS,
    ThreadMapper::CLASS,
    TaggingMapper::CLASS,
]);
```

Finaly, get back the Atlas instance out of the container.

```
<?php
$atlas = $atlasContainer->getAtlas();
```

