Table Events and Mapper Events.

- transform data on selection, before going into Row

- sanitize/validate Records and Rows; throw exception to bail out.

- check the database for presence/nonpresence of values (uniqueness) -- part
  of validation

- auto-set field on insert/update, e.g. created_on, updated_on

- increment/decrement a Record field -- via events, and select back the new
  count

- update *other* records on insert/update/delete; e.g. trees/lists/etc
