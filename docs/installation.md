# Installation

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
help create skeleton classes for the mapper, and works with both the 1.x and
2.x ORM packages.)
