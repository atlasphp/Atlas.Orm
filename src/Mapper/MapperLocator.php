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
     *
     * Returns the Exception for when a class is not available.
     *
     * @param string $class The class that was not found.
     *
     * @return Exception
     *
     */
    protected function notFoundException(string $class) : Exception
    {
        return Exception::mapperNotFound($class);
    }
}
