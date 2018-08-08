# CHANGELOG

## 3.1.0

- Added methods Atlas::logQueries() and Atlas::getQueries(), to expose
the query logging functionality of the ConnectionLocator.

- Updated docs and tests

## 3.0.0

- Added methods Atlas::newRecords() and Atlas::persistRecords()

- For consistency with other methods, Atlas::persistRecordSet() now returns
  void, and no longer detaches deleted records

- Updated docs

## 3.0.0-beta1

This release provides a PHPStorm metadata resource to aid in IDE autocompletion
of return typehints, found at `resources/phpstorm.meta.php`. Copy it to the root
of your project as `.phpstorm.meta.php`, or add it to your root-level
`.phpstorm.meta.php/` directory as `atlas.meta.php`.

Also, the documentation and tests for this package have been updated to honor
changes to the underlying Mapper and Table packages. In particular, the _Mapper_
classes no longer use a _Mapper_ suffix.

## 3.0.0-alpha1

Initial release of the 3.x series.
