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
