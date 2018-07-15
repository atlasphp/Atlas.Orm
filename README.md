# Atlas.Orm

Atlas is a [data mapper](http://martinfowler.com/eaaCatalog/dataMapper.html)
implementation for **persistence models** (*not* domain models).

As such, Atlas uses the term "record" to indicate that its objects are *not*
domain objects. Use Atlas records directly for simple data source interactions
at first. As a domain model grows within the application, use Atlas records to
populate domain objects. (Note that an Atlas record is a "passive" record, not an
[active record](http://martinfowler.com/eaaCatalog/activeRecord.html).
It is disconnected from the database.)

Documentation is at <http://atlasphp.io>.
