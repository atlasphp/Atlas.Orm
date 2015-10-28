<?php
namespace Atlas\DataSource\Tagging;

trait TaggingTableTrait
{
    /**
     * @inheritdoc
     */
    public function tableName()
    {
        return 'taggings';
    }

    /**
     * @inheritdoc
     */
    public function tableCols()
    {
        return [
            'tagging_id',
            'thread_id',
            'tag_id',
        ];
    }

    /**
     * @inheritdoc
     */
    public function tablePrimary()
    {
        return 'tagging_id';
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
            'tagging_id' => null,
            'thread_id' => null,
            'tag_id' => null,
        ];
    }
}
