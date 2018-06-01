- soft-deletion by marking a field using method on a custom Record, then have
  the Mapper add "where('soft_deleted = ', false)";

- overriding Row::assertValidValue() (e.g. to allow objects in Rows) -- might
  also be a be a method on a custom Record

- Table Events and Mapper Events.

    - update *other* records on insert/update/delete; e.g. trees/lists/etc

- Automatic validation approaches

    - check the database for presence/nonpresence of values (uniqueness) -- part
      of validation

- Dependency injection using the callable factory
