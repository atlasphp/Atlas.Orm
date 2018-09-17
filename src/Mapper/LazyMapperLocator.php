<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Orm\Mapper;

use Atlas\Orm\Exception;
use Atlas\Orm\Table\LazyTableLocator;
use Atlas\Orm\Relationship\Relationships;

/**
 *
 * A ServiceLocator to auto-create Mapper objects.
 *
 * @package atlas/orm
 *
 */
class LazyMapperLocator extends MapperLocator
{
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
     * @param TableLocator $tableLocator A table locator for the Mapper objects.
     *
     */
    public function __construct(LazyTableLocator $tableLocator)
    {
        parent::__construct($tableLocator);
        $this->factory = $this->tableLocator->getFactory();
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
                && is_subclass_of($class, AbstractMapper::CLASS, true)
            );
    }

    /**
     *
     * Gets a Mapper instance by class; if it has not been created yet, its
     * callable factory will be invoked and the instance will be retained.
     *
     * @param string $class The class of the instance to retrieve.
     *
     * @return MapperInterface An instance of the class.
     *
     * @throws Exception When the class is not available through this locator.
     *
     */
    public function get(string $class) : MapperInterface
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
     * Returns a new Mapper instance.
     *
     * @param string $mapperClass The Mapper class.
     *
     * @return MapperInterface.
     *
     */
    protected function newInstance(string $mapperClass) : MapperInterface
    {
        if (isset($this->factories[$mapperClass])) {
            return ($this->factories[$mapperClass])();
        }

        $tableClass = $mapperClass::getTableClass();
        if (! class_exists($tableClass)) {
            throw Exception::classDoesNotExist($tableClass);
        }

        $eventsClass = $mapperClass . 'Events';
        $eventsClass = class_exists($eventsClass)
            ? $eventsClass
            : MapperEvents::CLASS;

        return new $mapperClass(
            $this->tableLocator->get($tableClass),
            new Relationships($this),
            ($this->factory)($eventsClass)
        );
    }

    /**
     *
     * Returns the Exception for when a class is not available.
     *
     * @param string $mapperClass The class that was not found.
     *
     * @return Exception
     *
     */
    protected function notFoundException(string $mapperClass) : Exception
    {
        return Exception::mapperNotFound($mapperClass);
    }
}
