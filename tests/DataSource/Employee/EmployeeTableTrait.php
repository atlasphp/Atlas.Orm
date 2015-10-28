<?php
namespace Atlas\DataSource\Employee;

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
