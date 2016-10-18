<?php
namespace Atlas\Orm\DataSource\Test2;

use Atlas\Orm\Mapper\AbstractMapper;

/**
 * @inheritdoc
 */
class Test2Mapper extends AbstractMapper
{
    /**
     * @inheritdoc
     */
    protected function setRelated()
    {
        // no related fields
    }
}
