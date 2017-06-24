# Events

## Record Events

```php
<?php
namespace Blog\DataSource\Posts;

use Atlas\Orm\Mapper\MapperEvents;
use Atlas\Orm\Mapper\MapperInterface;
use Atlas\Orm\Mapper\RecordInterface;
use Atlas\Orm\Exception;

/**
 * @inheritdoc
 */
class PostsMapperEvents extends MapperEvents
{
    public function beforeUpdate(MapperInterface $mapper, RecordInterface $record)
    {
        // Validate the record and throw an exception if not valid.
        $required = [
            'title'=>'Post title',
            'body'=>'Post body',
            'author_id'=>'Author'
        ];
        foreach ($required as $field=>$label) {
            if (! $record->$field) {
                $record->addError($field, $label . ' can not be empty');
            }
        }

        if (count($record->getErrors()) > 0) {
            throw new Exception('Update Error');
        }

    }
}
```
