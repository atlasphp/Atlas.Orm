# CHANGELOG

This is the changelog for the 2.x series.

## 2.1.0

This release adds support for many-to-one relationships by reference (aka
"polymorphic association") in addition to some convenience and informational
methods.

- Documentation and code hygiene fixes

- Add method `Mapper\Related::getFields()`

- Add method `Mapper\RecordSet::removeAll()`

- Add method `Mapper\RecordSet::markForDeletion()`

- Add method `Relationship\Relationships::manyToOneByReference()`

- Add method `Mapper\AbstractMapper::manyToOneByReference()`

## 2.0.0

Documentation changes and updates.

## 2.0.0-beta1

MOTIVATION:

In 1.x, executing a Transaction::persist() will not work properly when the
relationships are mapped across connections that are not the same as the main
record. This is because the Transaction begins on the main record connection,
but does not have access to the connections for related records, and so cannot
track them. The only way to fix this is to introduce a BC break on the Table and
Transaction classes, both their constructors and their internal operations.

As long as BC breaks are on the table, this creates the opportunity to make
other changes, though with an eye to minimizing those changes to reduce the
hassle of moving from 1.x to 2.x.

UPGRADE NOTES FROM 1.x:

- This package now requires PHP 7.1 or later, and PHPUnit 6.x for development.
  Non-strict typehints have been added throughout, except in cases where they
  might break classes generated from 1.x.

- You *should not* need to modify any classes generated from 1.x; however, if
  you have overridden class methods in custom classes, you *may* need to modify
  that code to add typehints.

- This package continues to use Aura.Sql and Aura.SqlQuery 2.x; you *should not*
  need to change any queries.

- You *should not* need to change any calls to AtlasContainer for setup.

- The following methods now return `null` (instead of `false`) when they fail.
  You may need to change any logic checking for a strict `false` return value;
  checking for a loosely false-ish value will continue to work.

    - AbstractMapper::fetchRecord()
    - AbstractMapper::fetchRecordBy()
    - AbstractTable::fetchRow()
    - Atlas::fetchRecord()
    - Atlas::fetchRecordBy()
    - IdentityMap::getRow()
    - MapperInterface::fetchRecord()
    - MapperInterface::fetchRecordBy()
    - MapperSelect::fetchRecord()
    - RecordSet::getOneBy()
    - RecordSet::removeOneBy()
    - RecordSetInterface::getOneBy()
    - RecordSetInterface::removeOneBy()
    - Table::updateRowPerform()
    - TableInterface::fetchRow()
    - TableSelect::fetchOne()
    - TableSelect::fetchRow()

  (N.b.: Values for a single *related* record are still `false`, not `null`.
  That is, `null` still indicates "there was no attempt to fetch a related
  record," while `false` still indicates "there was an attempt to fetch a
  related record, but it did not exist.")

- The following methods will now *always* return a RecordSetInterface, even when
  no records are found. (Previously, they would return an empty array when no
  records were found.) To check for "no records found", call `isEmpty()` on the
  returned RecordSetInterface.

    - AbstractMapper::fetchRecordSet()
    - AbstractMapper::fetchRecordSetBy()
    - Atlas::fetchRecordSet()
    - Atlas::fetchRecordSetBy()
    - MapperInterface::fetchRecordSet()
    - MapperInterface::fetchRecordSetBy()
    - MapperSelect::fetchRecordSet()

OTHER CHANGES FROM 1.x:

- Added Atlas\Orm\Table\ConnectionManager to manage connections at a table-
  specific level.

    - Manages simultaneous transactions over multiple connections.

    - Allows setting of table-specific "read" and "write" connections.

    - Allows on-the-fly replacement of "read" connections with "write"
      connections while writing (useful for synchronizing reads with writes
      while in a transaction) or always (useful for GET-after-POST situations).

    - If the ConnectionManager starts a transaction on *one* connection (whether
      read or write) then it will start a tranasaction on *all* connections as
      they are retrieved.

- AbstractTable now uses the ConnectionManager instead of Aura.Sql
  ConnectionLocator, and *does not* retain (memoize) the connection objects.
  It retrieves them from the ConnectionManager each time they are needed; this
  helps maintain transaction state across multiple connections.

- Modified Transaction class to use the ConnectionManager, instead of tracking
  write connections on its own. This makes sure AbstractMapper::persist() will
  work properly with different related connections inside a transaction.

- The ManyToMany relationship now honors the order of the returned rows.

- Updated docs and tests.
