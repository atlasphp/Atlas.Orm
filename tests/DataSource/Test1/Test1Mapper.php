<?php
namespace Atlas\Orm\DataSource\Test1;

use Atlas\Orm\Mapper\AbstractMapper;
use Atlas\Orm\DataSource\Test2\Test2Mapper;

/**
 * @inheritdoc
 */
class Test1Mapper extends AbstractMapper
{
    /**
     * @inheritdoc
     */
    protected function setRelated()
    {
        // no related fields
        $this->oneToMany('test2', Test2Mapper::CLASS)
            ->on([
                'id' => 'test1_id'
            ]);
    }
}
