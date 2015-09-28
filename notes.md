Add checks on Row and Record types in the Table and Mapper.

Add a RecordInterface (and a RecordSet interface?) so that Domain objects can typehint against the interface, and not necessarily an actual record. Maybe also add a <Type>RecordInterface class that extends RecordInterface, and have <Type>Record implement it.

The relationships may be reusing record objects, rather than building new ones. Is that a problem?

Have the ManyToMany load the through-relationship if it's NULL.

More tests for when relationships are missing.

Allow the Relationship to create a new Row/Record (instead of `false`) or empty RowSet/RecordSet (instead of `[]`) when a relationship is empty.

Identity Field.

Complex primary keys.

Fetching strategies and identity lookups for compelex keys.

In RecordSets and Record relations, automatically set IdentityField when attaching.

Move away from ArrayObject for Sets and implement Countable, ArrayAccess, IteratorAggregate.

Add "append()" to Sets to append a new Row/Record of the proper type. Probably need to keep a reference back to the original Table/Mapper for the "new" logic.

Move Relationship aggregation/grouping logic out of RecordSet and into Relationship.

* * *

What we're going for is "Domain Model composed of Persistence Model". That is, the Domain entities/aggregates use Records and RecordSets internally, but never expose them. They can manipulate the PM internally as much as they wish. E.g., an CustomerEntity might have "getAddress()" and read from the internal CustomerRecord. Alternatively, we can do "DDD on top of ORM" where repositories map the Records to Entities/Aggregates.

Now, when you change the values on the Entity: if you have two instances of a particular CustomerEntity, and you change values on the CustomerRow, it's now reflected across all instances of that particular CustomerEntity, because the CustomerRow is identity-mapped. Is that a problem?

* * *

In generator, allow for:

    --table={tablename}
    --primary={primarycol}
    --autoinc={bool}

That will allow specification of pertinent values. It also means different templates for different classes.

Also allow for `--dir={dir}` so you don't need to `cd` into the right directory.

* * *

How to properly wrap an Atlas record for the Domain?

```php
<?php
namespace App\Domain\Thread;

use App\DataSource\ThreadRecord;

class ThreadAggregate
{
    protected $record;

    public function __construct(ThreadRecord $thread)
    {
        $this->record = $record;
    }

    public function getReplyCount()
    {
        return count($this->record->replies);
    }

    public function addReply($data)
    {
        $this->record->replies->appendNew($data);
    }
}
