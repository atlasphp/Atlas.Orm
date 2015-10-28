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
