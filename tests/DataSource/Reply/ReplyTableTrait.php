<?php
namespace Atlas\DataSource\Reply;

trait ReplyTableTrait
{
    /**
     * @inheritdoc
     */
    public function tableName()
    {
        return 'replies';
    }

    /**
     * @inheritdoc
     */
    public function tableCols()
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
    public function tableInfo()
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
    public function tablePrimary()
    {
        return 'reply_id';
    }

    /**
     * @inheritdoc
     */
    public function tableAutoinc()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function tableDefault()
    {
        return [
            'reply_id' => null,
            'thread_id' => null,
            'author_id' => null,
            'body' => null,
        ];
    }
}
