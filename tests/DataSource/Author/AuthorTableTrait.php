<?php
namespace Atlas\DataSource\Author;

trait AuthorTableTrait
{
    /**
     * @inheritdoc
     */
    public function tableName()
    {
        return 'authors';
    }

    /**
     * @inheritdoc
     */
    public function tableCols()
    {
        return [
            'author_id',
            'name',
        ];
    }

    /**
     * @inheritdoc
     */
    public function tableInfo()
    {
        return [
            'author_id' => (object) [
                'name' => 'author_id',
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
        return 'author_id';
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
            'author_id' => null,
            'name' => null,
        ];
    }
}
