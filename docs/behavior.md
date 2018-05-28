# Record and RecordSet Behaviors

Atlas makes it easy to add your own behaviors to both Records and RecordSets.

It's important to note that the Record and RecordSet objects described below
**should only be used for very simple behaviors**. Any non-trivial domain work
may be an indication that you need a domain layer. See the documentation on
[Domain Models](domain.html) for examples of how you can use Atlas to build a
domain layer.

For example:

```php
namespace App\DataSource\Thread;

use Atlas\Mapper\Record;

class ThreadRecord extends Record
{
    // Format the date_created property
    public function formatDate($format = 'M jS, Y')
    {
        $dateTime = new \DateTime($this->date_created);
        return $dateTime->format($format);
    }
}

$thread = $atlas->fetchRecord(Thread::CLASS, $id);
echo $thread->formatDate(); // outputs something like `Aug 21st, 2017`
```

The same concept is available for RecordSets using the RecordSet class. In our
example `ThreadRecordSet.php`.

```php
namespace App\DataSource\Thread;

use Atlas\Mapper\RecordSet;

class ThreadRecordSet extends RecordSet
{
    public function getAllTitles()
    {
        $titles = []
        foreach ($this as $record) {
            $titles[$record->thread_id] = $record->title;
        }
        return $titles;
    }
}
$threads = $atlas->fetchRecordSet(Thread::CLASS, [1, 2, 3]);

print_r($threads->getAllTitles());
```
