<?php
namespace Atlas\Orm\DataSource\Tagging;

use Atlas\Orm\Table\AbstractTable;

class TaggingTable extends AbstractTable
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
    public function tableInfo()
    {
        return [
            'tagging_id' => (object) [
                'name' => 'tagging_id',
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
            'tag_id' => (object) [
                'name' => 'tag_id',
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
