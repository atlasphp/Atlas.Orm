Things that happen when interacting with a record:

- increment/decrement a field

- convert field to object and back again, e.g. Date object

- update *other* records on insert/update/delete; e.g. trees/lists/etc

- auto-set field on insert/update, e.g. created_on, updated_on

- soft-deletion by marking a field

- check the database for presence/nonpresence of values (uniqueness)

- single-table inheritance.
