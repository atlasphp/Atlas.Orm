# Record and RecordSet Behaviors

Atlas makes it easy to add your own behaviors to both Records and RecordSets. To
accomplish this, you need a Record class for custom Record logic, and a
RecordSet class for custom RecordSet logic. The Atlas CLI script
(installable via composer using `atlas/cli`), can create these classes for you,
saving you from manually writing them.

Consult the [Atlas CLI documentation](https://github.com/atlasphp/Atlas.Cli/blob/1.x/README.md).

It's important to note that the Record and RecordSet objects described below
**should only be used for very simple behaviors**. Any non-trivial domain work
may be an indication that you need a domain layer. See the documentation on
[Domain Models](domain.html) for examples of how you can use Atlas to build a
domain layer.

Here is an example using the `atlas/cli` package and the `--full` option.

```
./vendor/bin/atlas-skeleton.php \
    --conn=/path/to/conn.php \
    --dir=src/App/DataSource \
    --table=threads \
    --full \
    App\\DataSource\\Thread
```

Upon completion, you will have a folder layout as follows:

```
-- src
   -- App
      -- DataSource
         -- Thread
            -- ThreadMapper.php
            -- ThreadMapperEvents.php
            -- ThreadRecord.php
            -- ThreadRecordSet.php
            -- ThreadTable.php
            -- ThreadTableEvents.php
```

Once you have a Record Class (`ThreadRecord.php`), you can create custom methods
to call from your Record object.

```php
<?php
namespace App\DataSource\Thread;

use Atlas\Orm\Mapper\Record;

/**
 * @inheritdoc
 */
class ThreadRecord extends Record
{
    // Format the date_created property
    public function formatDate($format = 'M jS, Y')
    {
        $dateTime = new \DateTime($this->date_created);
        return $dateTime->format($format);
    }
}
$thread = $atlas->fetchRecord(ThreadMapper::CLASS, $id);
echo $thread->formatDate(); // outputs something like `Aug 21st, 2017`
```

The same concept is available for RecordSets using the RecordSet class. In our
example `ThreadRecordSet.php`.

```php
<?php
namespace App\DataSource\Thread;

use Atlas\Orm\Mapper\RecordSet;

/**
 * @inheritdoc
 */
class ThreadRecordSet extends RecordSet
{
    public function foo()
    {
        $data = []
        foreach ($this as $record) {
            $data[] = $record->title;
        }

        return implode('; ', $data);
    }
}
$threads = $atlas->fetchRecordSet(ThreadMapper::CLASS, [1, 2, 3]);

echo $threads->foo();
```
