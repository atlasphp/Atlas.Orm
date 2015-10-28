<?php
namespace Atlas\DataSource\Thread;

trait ThreadTableTrait
{
    /**
     * @inheritdoc
     */
    public function tableName()
    {
        return 'threads';
    }

    /**
     * @inheritdoc
     */
    public function tableCols()
    {
        return [
            'thread_id',
            'author_id',
            'subject',
            'body',
        ];
    }

    /**
     * @inheritdoc
     */
    public function tablePrimary()
    {
        return 'thread_id';
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
            'thread_id' => null,
            'author_id' => null,
            'subject' => null,
            'body' => null,
        ];
    }
}
