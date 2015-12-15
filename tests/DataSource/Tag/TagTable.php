<?php
namespace Atlas\Orm\DataSource\Tag;

use Atlas\Orm\Table\AbstractTable;

class TagTable extends AbstractTable
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
    public function tableInfo()
    {
        return [
            'tag_id' => (object) [
                'name' => 'tag_id',
                'type' => 'integer',
                'size' => null,
                'scale' => null,
                'notnull' => false,
                'default' => null,
                'autoinc' => true,
                'primary' => true,
            ],
            'name' => (object) [
                'name' => 'name',
                'type' => 'varchar',
                'size' => 50,
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
