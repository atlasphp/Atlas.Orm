<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Orm\Mapper;

use Atlas\Orm\AbstractLocator;
use Atlas\Orm\Exception;

/**
 *
 * A ServiceLocator for Mapper objects.
 *
 * @package atlas/orm
 *
 */
class MapperLocator extends AbstractLocator
{
    /**
     * @inheritdoc
     */
    protected function notFoundException($class)
    {
        return Exception::mapperNotFound($class);
    }
}
