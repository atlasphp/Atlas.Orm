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
use Atlas\Orm\Table\TableLocator;

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
     * The same table locator used by the Mapper objects.
     *
     * @var TableLocator
     *
     */
    protected $tableLocator;

    /**
     *
     * Constructor.
     *
     * @param TableLocator $tableLocator The same table locator used by the
     * Mapper objects.
     *
     */
    public function __construct(TableLocator $tableLocator = null)
    {
        $this->tableLocator = $tableLocator;
    }

    /**
     *
     * Gets the table locator.
     *
     * @return TableLocator
     *
     */
    public function getTableLocator() : TableLocator
    {
        return $this->tableLocator;
    }

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
