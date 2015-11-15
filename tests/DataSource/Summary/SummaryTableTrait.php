<?php
namespace Atlas\Orm\DataSource\Summary;

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
    public function tableInfo()
    {
        return [
            'thread_id' => (object) [
                'name' => 'thread_id',
                'type' => 'integer',
                'size' => null,
                'scale' => null,
                'notnull' => false,
                'default' => null,
                'autoinc' => false,
                'primary' => true,
            ],
            'reply_count' => (object) [
                'name' => 'reply_count',
                'type' => 'integer',
                'size' => null,
                'scale' => null,
                'notnull' => true,
                'default' => null,
                'autoinc' => false,
                'primary' => false,
            ],
            'view_count' => (object) [
                'name' => 'view_count',
                'type' => 'integer',
                'size' => null,
                'scale' => null,
                'notnull' => true,
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
