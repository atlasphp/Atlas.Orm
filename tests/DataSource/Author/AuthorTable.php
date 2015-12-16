<?php
namespace Atlas\Orm\DataSource\Author;

use Atlas\Orm\Table\TableInterface;

class AuthorTable implements TableInterface
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'authors';
    }

    /**
     * @inheritdoc
     */
    public function getColNames()
    {
        return [
            'author_id',
            'name',
        ];
    }

    /**
     * @inheritdoc
     */
    public function getCols()
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
    public function getPrimaryKey()
    {
        return 'author_id';
    }

    /**
     * @inheritdoc
     */
    public function getAutoinc()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getColDefaults()
    {
        return [
            'author_id' => null,
            'name' => null,
        ];
    }
}
