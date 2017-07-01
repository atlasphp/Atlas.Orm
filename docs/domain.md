# Domain Models

You can go a long way with just your persistence model Records. However, at some
point you may want to separate your persistence model Records from your domain
model Entities and Aggregates. This section offers some suggestions and examples
on how to do that.

## Persistence Model

For the examples below, we will work with an imaginary forum application that
has conversation threads. The ThreadMapper might something like this:

```php
<?php
namespace App\DataSource\Thread;

use App\DataSource\Author\AuthorMapper;
use App\DataSource\Summary\SummaryMapper;
use App\DataSource\Reply\ReplyMapper;
use App\DataSource\Tagging\TaggingMapper;
use App\DataSource\Tag\TagMapper;
use Atlas\Orm\Mapper\AbstractMapper;

class ThreadMapper extends AbstractMapper
{
    protected function setRelated()
    {
        $this->manyToOne('author', AuthorMapper::CLASS);
        $this->oneToOne('summary', SummaryMapper::CLASS);
        $this->oneToMany('replies', ReplyMapper::CLASS);
        $this->oneToMany('taggings', TaggingMapper::CLASS);
        $this->manyToMany('tags', TagMapper::CLASS, 'taggings');
    }
}
```

(We will leave the other mappers and their record classes for the imagination.)

## Domain Model Interfaces

At some point, we have decided we want to depend on domain Entities or
Aggregates, rather than persistence Records, in our application.

For example, the interface we want to use for a Thread Entity in domain might
look like this:

```php
<?php
namespace App\Domain\Thread;

interface ThreadInterface
{
    public function getId();
    public function getSubject();
    public function getBody();
    public function getDatePublished();
    public function getAuthorId();
    public function getAuthorName();
    public function getTags();
    public function getReplies();
}
```

(This interface allows us to typehint the application against these domain-
specific Entity methods, rather than using the persistence Record properties.)

Further, we will presume a naive domain repository implementation that returns
Thread Entities. It might look something like this:

```php
<?php
namespace App\Domain\Thread;

use App\DataSource\Thread\ThreadMapper;

class ThreadRepository
{
    protected $mapper;

    public function __construct(ThreadMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    public function fetchThread($thread_id)
    {
        $record = $this->mapper->fetchRecord($thread_id, [
            'author',
            'taggings',
            'tags',
            'replies',
        ]);

        return $this->newThread($record);
    }

    protected function newThread(ThreadRecord $record)
    {
        /* ??? */
    }
}
```

The problem now is the `newThread()` factory method. How do we convert a
persistence layer ThreadRecord into a domain layer ThreadInterface
implementation?

There are three options, each with different tradeoffs:

1. Implement the domain interface in the persistence layer.
2. Compose the persistence record into the domain object.
3. Map the persistence record fields to domain implementation fields.

## Implement Domain In Persistence

The easiest thing to do is to implement the domain ThreadInterface in the
persistence ThreadRecord, like so:

```php
<?php
namespace App\DataSource\Thread;

use Atlas\Orm\Mapper\Record;
use App\Domain\Thread\ThreadInterface;

class ThreadRecord extends Record implements ThreadInterface
{
    public function getId()
    {
        return $this->thread_id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getDatePublished()
    {
        return $this->date_published;
    }

    public function getAuthorId()
    {
        return $this->author->author_id;
    }

    public function getAuthorName()
    {
        return $this->author->name;
    }

    public function getTags()
    {
        return $this->tags->getArrayCopy();
    }

    public function getReplies()
    {
        return $this->replies->getArrayCopy();
    }
}
```

With this, the `ThreadRepository::newThread()` factory method doesn't actually
need to factory anything at all. It just returns the persistence record, since
the record now has the domain interface.

```php
<?php
class ThreadRepository ...

    protected function newThread(ThreadRecord $record)
    {
        return $record;
    }
```

Pros:

- Trivial to implement.

Cons:

- Exposes the persistence layer Record methods and properties to the domain
  layer, where they can be easily abused.

## Compose Persistence Into Domain

Almost as easy, but with better separation, is to have a domain layer object
that implements the domain interface, but encapsulates the persistence record
as the data source. The domain object might look something like this:

```php
<?php
namespace App\Domain\Thread;

use App\DataSource\Thread\ThreadRecord;

class Thread implements ThreadInterface
{
    protected $record;

    public function __construct(ThreadRecord $record)
    {
        $this->record = $record;
    }

    public function getId()
    {
        return $this->record->thread_id;
    }

    public function getTitle()
    {
        return $this->record->title;
    }

    public function getBody()
    {
        return $this->record->body;
    }

    public function getDatePublished()
    {
        return $this->record->date_published;
    }

    public function getAuthorId()
    {
        return $this->record->author->author_id;
    }

    public function getAuthorName()
    {
        return $this->record->author->name;
    }

    public function getTags()
    {
        return $this->record->tags->getArrayCopy();
    }

    public function getReplies()
    {
        return $this->record->replies->getArrayCopy();
    }
}
```

Now the `ThreadRepository::newThread()` factory method has to do a little work,
but not much. All it needs is to create the Thread domain object with the
ThreadRecord as a constructor dependency.

```php
<?php
class ThreadRepository ...

    protected function newThread(ThreadRecord $record)
    {
        return new Thread($record);
    }
```

Pros:

- Hides the persistence record behind the domain interface.
- Easy to implement.

Cons:

- The domain object is now dependent on the persistence layer, which is not the
  direction of dependencies we'd prefer.


## Map From Persistence To Domain

Most difficult, but with the best separation, is to map the individual parts of
the persistence record over to a "plain old PHP object" (POPO) in the domain,
perhaps something like the following:

```php
<?php
namespace App\Domain\Thread;

use App\DataSource\Thread\ThreadRecord;

class Thread implements ThreadInterface
{
    protected $id;
    protected $title;
    protected $body;
    protected $datePublished;
    protected $authorId;
    protected $authorName;
    protected $tags;
    protected $replies;

    public function __construct(
        $id,
        $title,
        $body,
        $datePublished,
        $authorId,
        $authorName,
        array $tags,
        array $replies
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->body = $body;
        $this->datePublished = $datePublished;
        $this->authorId = $authorId;
        $this->authorName = $authorName;
        $this->tags = $tags;
        $this->replies = $replies;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getDatePublished()
    {
        return $this->datePublished;
    }

    public function getAuthorId()
    {
        return $this->authorId;
    }

    public function getAuthorName()
    {
        return $this->authorName;
    }

    public function getTags()
    {
        return $this->tags;
    }

    public function getReplies()
    {
        return $this->replies;
    }
}
```

Now the `ThreadRepository::newThread()` factory method has a lot of work to do.
It needs to map the individual fields in the persistence record to the domain
object properties.

```php
<?php
class ThreadRepository ...

    protected function newThread(ThreadRecord $record)
    {
        return new Thread(
            $record->thread_id,
            $record->title,
            $record->body,
            $record->date_published,
            $record->author->author_id,
            $record->author->name,
            $record->tags->getArrayCopy(),
            $record->replies->getArrayCopy()
        );
    }

```

Pros:

- Offers true separation of domain from persistence.

Cons:

- Tedious and time-consuming to implement.

## Which Approach Is Best?

"It depends." What does it depend on?  How much time you have available, and what kind of
suffering you are willing to put up with.

If you need something quick, fast, and in a hurry, implementing the domain
interface in the persistence layer will do the trick. However, it will come back
to bite in you just as quickly, as you begin to realize that you need different
domain behaviors in different contexts, all built from the same backing
persistence records.

If you are willing to deal with the trouble that comes from depending on the
persistence layer records inside your domain, and the possibility that other
developers will expose the underlying record in subtle ways, then composing the
record into the domain may be your best bet.

The most formally-correct approach is to map the record fields over to domain
object properties. This level of separation makes testing and modification of
application logic much easier in the long run, but it takes a lot of time,
attention, and discipline.
