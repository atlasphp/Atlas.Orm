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

    public function set($tableClass, callable $factory)
    {
        $this->factories[$tableClass] = $factory;
    }

    public function has($tableClass)
    {
        return isset($this->factories[$tableClass]);
    }

    /**
     *
     * Gets a Table instance by class; if it has not been created yet, its
     * callable factory will be invoked and the instance will be retained.
     *
     * @param string $tableClass The class of the Table instance to retrieve.
     *
     * @return Table A Table instance.
     *
     * @throws Exception When a Table instance is not found.
     *
     */
    public function get($tableClass)
    {
        if (! isset($this->factories[$tableClass])) {
            throw Exception::tableNotFound($tableClass);
        }

        if (! isset($this->instances[$tableClass])) {
            $factory = $this->factories[$tableClass];
            $this->instances[$tableClass] = call_user_func($factory);
        }

        return $this->instances[$tableClass];
    }
}
