<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Orm;

/**
 *
 * An abstract ServiceLocator implementation.
 *
 * @package atlas/orm
 *
 */
abstract class AbstractLocator
{
    /**
     *
     * A registry of callable factories to create object instances.
     *
     * @var array
     *
     */
    protected $factories = [];

    /**
     *
     * A registry of object instances created by the factories.
     *
     * @var array
     *
     */
    protected $instances = [];

    /**
     *
     * Sets a factory for a class name.
     *
     * @param string $class The class to instantiate.
     *
     * @param callable $factory The factory callable.
     *
     */
    public function set(string $class, callable $factory) : void
    {
        $this->factories[$class] = $factory;
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
        return isset($this->factories[$class]);
    }

    /**
     *
     * Gets an object instance by class; if it has not been created yet, its
     * callable factory will be invoked and the instance will be retained.
     *
     * @param string $class The class of the instance to retrieve.
     *
     * @return object An instance of the class.
     *
     * @throws Exception When the class is not available through this locator.
     *
     */
    public function get(string $class)
    {
        if (! isset($this->factories[$class])) {
            throw $this->notFoundException($class);
        }

        if (! isset($this->instances[$class])) {
            $this->instances[$class] = call_user_func($this->factories[$class]);
        }

        return $this->instances[$class];
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
    abstract protected function notFoundException(string $class) : Exception;
}
