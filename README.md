# Atlas.Orm

> No annotations. No migrations. No lazy loading. No data-type abstractions.

Atlas is a [data mapper](http://martinfowler.com/eaaCatalog/dataMapper.html)
implementation for your **persistence model** (*not* your domain model).

As such, Atlas uses the term "record" to indicate that its objects are *not*
domain entities. Note that an Atlas record is a *passive* record, not an [active
record](http://martinfowler.com/eaaCatalog/activeRecord.html); it is
disconnected from the database. Use Atlas records as a way to populate your
domain entities, or use them directly for simple data source interactions.

Atlas is ready for side-project and experimental use. Please send bug reports
and pull requests!

Documentation is in [./docs](./docs/index.md).
