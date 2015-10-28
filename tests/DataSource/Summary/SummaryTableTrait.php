<?php
namespace Atlas\DataSource\Summary;

trait SummaryTableTrait
{
    /**
     * @inheritdoc
     */
    public function tableName()
    {
        return 'summaries';
    }

    /**
     * @inheritdoc
     */
    public function tableCols()
    {
        return [
            'thread_id',
            'reply_count',
            'view_count',
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
        return false;
    }

    /**
     * @inheritdoc
     */
    public function tableDefault()
    {
        return [
            'thread_id' => null,
            'reply_count' => null,
            'view_count' => null,
        ];
    }
}
