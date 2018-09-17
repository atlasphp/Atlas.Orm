<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Orm\Table;

use Atlas\Orm\Exception;
use Aura\SqlQuery\QueryFactory;

/**
 *
 * A ServiceLocator to auto-create Table objects.
 *
 * @package atlas/orm
 *
 */
class LazyTableLocator extends TableLocator
{
    /**
     *
     * A query factory.
     *
     * @var QueryFactory
     *
     */
    protected $queryFactory;

    /**
     *
     * A custom object factory.
     *
     * @var callable
     *
     */
    protected $factory;

    /**
     *
     * Constructor.
     *
     * @param ConnectionManager $connectionManager The same connection manager
     * used by the Table objects.
     *
     * @param QueryFactory A query factory.
     *
     * @param callable A custom object factory.
     *
     */
    public function __construct(
        ConnectionManager $connectionManager,
        QueryFactory $queryFactory,
        callable $factory
    ) {
        parent::__construct($connectionManager);
        $this->queryFactory = $queryFactory;
        $this->factory = $factory;
    }

    /**
     *
     * Gets the custom object facotry.
     *
     * @return callable
     *
     */
    public function getFactory() : callable
    {
        return $this->factory;
    }

    /**
     *
     * Can the locator return an instance of a particular class?
     *
     * @param string $class The class name.
     *
     * @return bool
     *
     */
    public function has(string $class) : bool
    {
        return
            isset($this->factories[$class])
            || (
                class_exists($class)
                && is_subclass_of($class, AbstractTable::CLASS, true)
            );
    }

    /**
     *
     * Gets a Table instance by class; if it has not been created yet, its
     * callable factory will be invoked and the instance will be retained.
     *
     * @param string $class The class of the instance to retrieve.
     *
     * @return TableInterface An instance of the class.
     *
     * @throws Exception When the class is not available through this locator.
     *
     */
    public function get(string $class) : TableInterface
    {
        if (! $this->has($class)) {
            throw $this->notFoundException($class);
        }

        if (! isset($this->instances[$class])) {
            $this->instances[$class] = $this->newInstance($class);
        }

        return $this->instances[$class];
    }

    /**
     *
     * Returns a new Table instance.
     *
     * @param string $tableClass The table class.
     *
     * @return TableInterface
     *
     */
    protected function newInstance(string $tableClass) : TableInterface
    {
        $eventsClass = $tableClass . 'Events';

        $eventsClass = class_exists($eventsClass)
            ? $eventsClass
            : TableEvents::CLASS;

        return new $tableClass(
            $this->connectionManager,
            $this->queryFactory,
            new IdentityMap(),
            ($this->factory)($eventsClass)
        );
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
