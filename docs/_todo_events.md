Table Events and Mapper Events.

- transform data on selection, before going into Row

- sanitize/validate Records and Rows; throw exception to bail out.

- check the database for presence/nonpresence of values (uniqueness) -- part
  of validation

- auto-set field on insert/update, e.g. created_on, updated_on

- increment/decrement a Record field -- via events, and select back the new
  count. alternatively, make these mapper/transaction methods:

    // $record->$field += $increment;
    // if $record already inserted ...
    // `UPDATE table SET field = field $incr WHERE primarykey`
    // if not inserted yet, just increment in memory
    $mapper->increment($record, 'field' [, $incr]);

    // *plans* an increment method call
    $transaction->increment($record, 'field' [, $incr]);

- update *other* records on insert/update/delete; e.g. trees/lists/etc
