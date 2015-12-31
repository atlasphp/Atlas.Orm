<?php
namespace Atlas\Orm\Table;

use Atlas\Orm\Exception;

/**
 *
 * A ServiceLocator implementation for creating and retaining Gateway objects.
 *
 * @package Atlas.Atlas
 *
 */
class GatewayLocator
{
    /**
     *
     * A registry of callable factories to create Gateway instances.
     *
     * @var array
     *
     */
    protected $factories = [];

    /**
     *
     * A registry of Gateway instances created by the factories.
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
     * Gets a Gateway instance by class; if it has not been created yet, its
     * callable factory will be invoked and the instance will be retained.
     *
     * @param string $tableClass The class of the Gateway instance to retrieve.
     *
     * @return Gateway A Gateway instance.
     *
     * @throws Exception When a Gateway instance is not found.
     *
     */
    public function get($tableClass)
    {
        if (! isset($this->factories[$tableClass])) {
            throw Exception::gatewayNotFound($tableClass);
        }

        if (! isset($this->instances[$tableClass])) {
            $factory = $this->factories[$tableClass];
            $this->instances[$tableClass] = call_user_func($factory);
        }

        return $this->instances[$tableClass];
    }
}
