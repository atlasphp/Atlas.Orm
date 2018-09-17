<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Orm\Table;

use Atlas\Orm\AbstractLocator;
use Atlas\Orm\Exception;

/**
 *
 * A ServiceLocator for Table objects.
 *
 * @package atlas/orm
 *
 */
class TableLocator extends AbstractLocator
{
    /**
     *
     * The same connection manager used by the Table objects.
     *
     * @var ConnectionManager
     *
     */
    protected $connectionManager;

    /**
     *
     * Constructor.
     *
     * @param ConnectionManager $connectionManager The same connection manager
     * used by the Table objects.
     *
     */
    public function __construct(ConnectionManager $connectionManager = null)
    {
        $this->connectionManager = $connectionManager;
    }

    /**
     *
     * Gets the connection manager.
     *
     * @return ConnectionManager
     *
     */
    public function getConnectionManager() : ConnectionManager
    {
        return $this->connectionManager;
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
        return Exception::tableNotFound($class);
    }
}
