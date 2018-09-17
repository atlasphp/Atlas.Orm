<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Orm;

use Atlas\Orm\Mapper\LazyMapperLocator;
use Atlas\Orm\Table\ConnectionManager;
use Atlas\Orm\Table\LazyTableLocator;
use Aura\Sql\ConnectionLocator;
use Aura\Sql\ExtendedPdo;
use Aura\SqlQuery\QueryFactory;
use PDO;

/**
 *
 * A builder for setting up Atlas with lazy-loaded mapper factories (as vs
 * registering them in advance).
 *
 * @package atlas/orm
 *
 */
class AtlasBuilder
{
    /**
     *
     * A manager for table-specific database connections.
     *
     * @var ConnectionManager
     *
     */
    protected $connectionManager;

    /**
     *
     * Custom object factory.
     *
     * @var array
     *
     */
    protected $factory;

    /**
     *
     * A factory for query objects.
     *
     * @var QueryFactory
     *
     */
    protected $queryFactory;

    /**
     *
     * Constructor.
     *
     * @param string|PDO|ExtendedPdo|ConnectionLocator $dsn The data source name
     * for a default lazy PDO connection, or an existing database connection or
     * connection locator. If non-string, the remaining params are ignored.
     *
     * @param string $username The default database connection username.
     *
     * @param string $password The default database connection password.
     *
     * @param array $options The default database connection options.
     *
     * @param array $attributes The default database connection attributes.
     *
     * @see ExtendedPdo::__construct()
     *
     */
    public function __construct(
        $dsn,
        string $username = null,
        string $password = null,
        array $options = [],
        array $attributes = []
    ) {
        $driver = $this->setConnectionManager(func_get_args());
        $this->queryFactory = new QueryFactory($driver);
        $this->factory = function (string $class) {
            return new $class();
        };
    }

    /**
     *
     * Creates a new Atlas instance.
     *
     * @return Atlas
     *
     */
    public function newAtlas() : Atlas
    {
        $mapperLocator = new LazyMapperLocator(
            new LazyTableLocator(
                $this->connectionManager,
                $this->queryFactory,
                $this->factory
            )
        );

        $transaction = new Transaction(
            $this->connectionManager,
            $mapperLocator
        );

        return new Atlas($mapperLocator, $transaction);
    }

    /**
     *
     * Sets a custom object factory with the signature
     * `function (string $class) // : object`.
     *
     * @param callable $factory A callable to create and return objects,
     * typically from a DI container of some sort.
     *
     */
    public function setFactory(callable $factory) : void
    {
        $this->factory = $factory;
    }

    /**
     *
     * Returns the connection locator.
     *
     * @return ConnectionLocator
     *
     */
    public function getConnectionLocator() : ConnectionLocator
    {
        return $this->getConnectionManager()->getConnectionLocator();
    }

    /**
     *
     * Returns the table-level connection manager.
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
     * Sets a new "read" connection by name into the connection locator.
     *
     * @param string $name The connection name.
     *
     * @param callable $callable A callable to create and return the connection.
     *
     */
    public function setReadConnection(string $name, callable $callable) : void
    {
        $this->getConnectionLocator()->setRead($name, $callable);
    }

    /**
     *
     * Sets a new "write" connection by name into the connection locator.
     *
     * @param string $name The connection name.
     *
     * @param callable $callable A callable to create and return the connection.
     *
     */
    public function setWriteConnection(string $name, callable $callable) : void
    {
        $this->getConnectionLocator()->setWrite($name, $callable);
    }

    /**
     *
     * Sets a single read connection for a table.
     *
     * @param string $tableClass The table class that will use the connection.
     *
     * @param string $name The connection name in the ConnectionLocator.
     *
     */
    public function setReadConnectionForTable(string $tableClass, string $name) : void
    {
        $this->connectionManager->setRead($tableClass, $name);
    }

    /**
     *
     * Sets a single read connection for a table.
     *
     * @param string $tableClass The table class that will use the connection.
     *
     * @param string $name The connection name in the ConnectionLocator.
     *
     */
    public function setWriteConnectionForTable(string $tableClass, string $name) : void
    {
        $this->connectionManager->setWrite($tableClass, $name);
    }

    /**
     *
     * When, if ever, should reads occur over write connections?
     *
     * @param string $readFromWrite 'ALWAYS', 'WHILE_WRITING', or 'NEVER'.
     *
     */
    public function setReadFromWrite(string $readFromWrite) : void
    {
        $this->connectionManager->setReadFromWrite($readFromWrite);
    }

    /**
     *
     * Creates and sets the connection locator with a default connection.
     *
     * @param array $args Params for an building a default connection.
     *
     * @see ExtendedPdo::__construct()
     *
     */
    protected function setConnectionManager(array $args) : string
    {
        $connectionLocator = new ConnectionLocator();

        switch (true) {

            case $args[0] instanceof ConnectionLocator:
                $connectionLocator = $args[0];
                // note that this actually opens the connection
                $driver = $connectionLocator->getDefault()->getAttribute(PDO::ATTR_DRIVER_NAME);
                break;

            case $args[0] instanceof ExtendedPdo:
                $extendedPdo = $args[0];
                $default = function () use ($extendedPdo) {
                    return $extendedPdo;
                };
                $driver = $extendedPdo->getAttribute(PDO::ATTR_DRIVER_NAME);
                $connectionLocator->setDefault($default);
                break;

            case $args[0] instanceof PDO:
                $pdo = $args[0];
                $default = function () use ($pdo) {
                    return new ExtendedPdo($pdo);
                };
                $driver = $pdo->getAttribute(ExtendedPdo::ATTR_DRIVER_NAME);
                $connectionLocator->setDefault($default);
                break;

            default:
                $default = function () use ($args) {
                    return new ExtendedPdo(...$args);
                };
                $parts = explode(':', $args[0]);
                $driver = array_shift($parts);
                $connectionLocator->setDefault($default);
                break;
        }

        $this->connectionManager = new ConnectionManager($connectionLocator);
        return $driver;
    }
}
