<?php
namespace Atlas\Orm\DataSource\Tag;

use Atlas\Orm\Table\TableInterface;

class TagTable implements TableInterface
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'tags';
    }

    /**
     * @inheritdoc
     */
    public function getColNames()
    {
        return [
            'tag_id',
            'name',
        ];
    }

    /**
     * @inheritdoc
     */
    public function getCols()
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
    public function getPrimary()
    {
        return 'tag_id';
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
            'tag_id' => null,
            'name' => null,
        ];
    }
}
