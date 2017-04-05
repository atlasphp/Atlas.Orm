# CHANGELOG

## 1.0.0

- Add TableSelect::fetchCount().

- Add RecordSet::appendNew(), getOneBy(), getAllBy(), removeOneBy(), and
  removeAllBy()

- Rename AbstractTable::insert(), update(), delete() to insertRow(),
  updateRow(), deleteRow() ...

- ... then rename AbstractTable::newInsert() to insert() , newUpdate() to
  update(), newDelete() to delete(); this is in line with the pre-existing
  select() method returning a new select object.

- Row, Record, and RecordSet now implement JsonSerializable

- Expand MapperEventsInterface to allow modifyInsert() etc.

- Relax set() on Row, Record, and Related, to allow for non-fields in the
  setting array

- Mapper::newRecord() now allows Related values in addition to Row values

- Row::modify() now restricts to scalar or null

- Related is now more strict: accepts only null, false, [], and RecordSet

- Added MapperSelect::joinWith() to allows joins to relateds, without fetching

- In AtlasContainer::__construct() et al, allow for pre-existing ExtendedPdo and
  PDO connections

- Add TableEvents::modifySelectedRow() to allow changes to rows coming from the
  database

- MapperSelect now throws an exception when you call with() using a non-existent
  related name

- AbstractMapper no longer allows related names to override column names

- MapperSelect::with() now allows for nested arrays (in addition to anonymous
  functions)

## 1.0.0-alpha1

First 1.x alpha release.
