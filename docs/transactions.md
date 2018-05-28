# Transactions

Atlas always starts in "autocommit" mode, which means that each interaction with
the database is its own micro-transaction (cf. <https://secure.php.net/manual/en/pdo.transactions.php>).

## Manual Transaction Management

You can manage transactions manually by calling these methods on the Atlas object:

```php
// begins a transaction on BOTH the read connection
// AND the write connection
$atlas->beginTransaction();

// commits the transaction on BOTH connections
$atlas->commit();

// rolls back the transaction on BOTH connections
$atlas->rollBack();
```

Once you perform a write operation (insert, update, delete, or persist), Atlas
will lock to the write connection. That is, all reads will occur against the
write connection for the rest of the Atlas object's lifetime.

## Other Transaction Strategies

If you find that manually managing transactions proves tedious, Atlas comes with
three alternative transaction strategy classes:

- _AutoTransact_ will automatically begin a transaction when you perform a
  write operation, then automatically commit that operation, or roll it back on
  exception. (Note that in the case of `persistRecordSet()`, each Record in the
  RecordSet will be persisted within its own transaction.)

- _BeginOnWrite_ will automatically begin a transaction when you perform a
  write operation. It will not commit or roll back; you will need to do so
  yourself. Once you do, the next time you perform a write operation, Atlas will
  begin another transaction.

- _BeginOnRead_ will automatically begin a transaction when you perform a
  write operation **or** a read operation. It will not commit or roll back; you
  will need to do so yourself. Once you do, the next time you perform a write or
  read operation, Atlas will begin another transaction.

(As with the manual strategy, the transactions are started on BOTH read and
write connections, and each of these will lock to the write connection once a
write operation is performed.)

To specify which transaction strategy to use, pass it as the last argument to
the _Atlas_ static `new()` call ...

```php
use Atlas\Orm\Atlas;
use Atlas\Orm\Transaction\AutoTransact;

// use a MiniTransaction strategy
$atlas = Atlas::new(
    'mysql:host=localhost;dbname=testdb',
    'username',
    'password',
    AutoTransact::CLASS
);
```

... or call `setTransactionClass()` on an _AtlasBuilder_ instance:

```php
use Atlas\Orm\AtlasBuilder;
use Atlas\Orm\Transaction\BeginOnRead;

$builder = new AtlasBuilder(
    'mysql:host=localhost;dbname=testdb',
    'username',
    'password'
);

// use a BeginOnRead strategy
$builder->setTransactionClass(BeginOnRead::CLASS);

// get a new Atlas instance
$atlas = $builder->newAtlas();
```
