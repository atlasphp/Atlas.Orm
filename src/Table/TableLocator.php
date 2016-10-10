<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @package atlas/orm
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Orm\Table;

use Atlas\Orm\Exception;

/**
 *
 * A ServiceLocator implementation for creating and retaining Table objects.
 *
 * @package atlas/orm
 *
 */
class TableLocator
{
    /**
     *
     * A registry of callable factories to create Table instances.
     *
     * @var array
     *
     */
    protected $factories = [];

    /**
     *
     * A registry of Table instances created by the factories.
     *
     * @var array
     *
     */
    protected $instances = [];

    public function set($class, callable $factory)
    {
        $this->factories[$class] = $factory;
    }

    public function has($class)
    {
        return isset($this->factories[$class]);
    }

    /**
     *
     * Gets a Table instance by class; if it has not been created yet, its
     * callable factory will be invoked and the instance will be retained.
     *
     * @param string $class The class of the Table instance to retrieve.
     *
     * @return Table A Table instance.
     *
     * @throws Exception When a Table instance is not found.
     *
     */
    public function get($class)
    {
        if (! isset($this->factories[$class])) {
            throw Exception::tableNotFound($class);
        }

        if (! isset($this->instances[$class])) {
            $this->instances[$class] = call_user_func($this->factories[$class]);
        }

        return $this->instances[$class];
    }
}
