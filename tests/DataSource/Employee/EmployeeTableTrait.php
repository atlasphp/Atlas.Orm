<?php
namespace Atlas\Orm\DataSource\Employee;

trait EmployeeTableTrait
{
    /**
     * @inheritdoc
     */
    public function tableName()
    {
        return 'employee';
    }

    /**
     * @inheritdoc
     */
    public function tableCols()
    {
        return [
            'id',
            'name',
            'building',
            'floor',
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
            'building' => (object) [
                'name' => 'building',
                'type' => 'integer',
                'size' => null,
                'scale' => null,
                'notnull' => false,
                'default' => null,
                'autoinc' => false,
                'primary' => false,
            ],
            'floor' => (object) [
                'name' => 'floor',
                'type' => 'integer',
                'size' => null,
                'scale' => null,
                'notnull' => false,
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
            'name' => null,
            'building' => null,
            'floor' => null,
        ];
    }
}
