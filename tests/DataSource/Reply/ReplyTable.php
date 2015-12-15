<?php
namespace Atlas\Orm\DataSource\Reply;

use Atlas\Orm\Table\TableInterface;

class ReplyTable implements TableInterface
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'replies';
    }

    /**
     * @inheritdoc
     */
    public function getColNames()
    {
        return [
            'reply_id',
            'thread_id',
            'author_id',
            'body',
        ];
    }

    /**
     * @inheritdoc
     */
    public function getCols()
    {
        return [
            'reply_id' => (object) [
                'name' => 'reply_id',
                'type' => 'integer',
                'size' => null,
                'scale' => null,
                'notnull' => false,
                'default' => null,
                'autoinc' => true,
                'primary' => true,
            ],
            'thread_id' => (object) [
                'name' => 'thread_id',
                'type' => 'integer',
                'size' => null,
                'scale' => null,
                'notnull' => true,
                'default' => null,
                'autoinc' => false,
                'primary' => false,
            ],
            'author_id' => (object) [
                'name' => 'author_id',
                'type' => 'integer',
                'size' => null,
                'scale' => null,
                'notnull' => true,
                'default' => null,
                'autoinc' => false,
                'primary' => false,
            ],
            'body' => (object) [
                'name' => 'body',
                'type' => 'text',
                'size' => null,
                'scale' => null,
                'notnull' => false,
                'default' => null,
                'autoinc' => false,
                'primary' => false,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function getPrimary()
    {
        return 'reply_id';
    }

    /**
     * @inheritdoc
     */
    public function getAutoinc()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getColDefaults()
    {
        return [
            'reply_id' => null,
            'thread_id' => null,
            'author_id' => null,
            'body' => null,
        ];
    }
}
