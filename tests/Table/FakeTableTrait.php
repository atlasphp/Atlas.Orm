<?php
namespace Atlas\Orm\Table;

trait FakeTableTrait
{
    /**
     * @inheritdoc
     */
    public function tableName()
    {
        return 'fakes';
    }

    /**
     * @inheritdoc
     */
    public function tableCols()
    {
        return [
            'id',
            'foo',
            'baz',
        ];
    }

    /**
     * @inheritdoc
     */
    public function tableInfo()
    {
        return [
            'id' => (object) [
                'name' => 'id',
                'type' => 'integer',
                'size' => null,
                'scale' => null,
                'notnull' => false,
                'default' => null,
                'autoinc' => true,
                'primary' => true,
            ],
            'foo' => (object) [
                'name' => 'foo',
                'type' => 'varchar',
                'size' => 50,
                'scale' => null,
                'notnull' => true,
                'default' => null,
                'autoinc' => false,
                'primary' => false,
            ],
            'baz' => (object) [
                'name' => 'baz',
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
        return 'id';
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
            'id' => null,
            'foo' => null,
            'baz' => null,
        ];
    }
}
