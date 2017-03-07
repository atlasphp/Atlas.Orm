# Installation

This package is installable and autoloadable via [Composer](https://getcomposer.org/)
as [atlas/orm](https://packagist.org/packages/atlas/orm).

Atlas is still in development and it is possible that the API may break, so you
may want to lock your `composer.json` to a
[particular release](https://github.com/atlasphp/Atlas.Orm/releases):

```json
{
    "require": {
        "atlas/orm": "0.3.*@alpha"
    }
}
```

Of course, if you want to keep up with the most recent unreleased changes, you can do the following:

```json
{
    "require": {
        "atlas/orm": "@dev"
    }
}
```

For ease of development you can add [atlas/cli](https://packagist.org/packages/atlas/cli)
in the `require-dev` section of `composer.json` in the root of your project. This will
provide the `atlas-skeleton` command-line tool.

```json
{
    "require-dev": {
        "atlas/cli": "@dev"
    }
}
```
