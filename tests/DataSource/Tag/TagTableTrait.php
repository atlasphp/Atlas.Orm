<?php
namespace Atlas\DataSource\Tag;

trait TagTableTrait
{
    /**
     * @inheritdoc
     */
    public function tableName()
    {
        return 'tags';
    }

    /**
     * @inheritdoc
     */
    public function tableCols()
    {
        return [
            'tag_id',
            'name',
        ];
    }

    /**
     * @inheritdoc
     */
    public function tablePrimary()
    {
        return 'tag_id';
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
            'tag_id' => null,
            'name' => null,
        ];
    }
}
