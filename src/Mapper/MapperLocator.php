<?php
namespace Atlas\Orm\Mapper;

use Atlas\Orm\Exception;

/**
 *
 * A ServiceLocator implementation for creating and retaining Mapper objects.
 *
 * @package Atlas.Atlas
 *
 */
class MapperLocator
{
    /**
     *
     * A registry of callable factories to create Mapper instances.
     *
     * @var array
     *
     */
    protected $factories = [];

    /**
     *
     * A registry of Mapper instances created by the factories.
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
     * Gets a Mapper instance by class; if it has not been created yet, its
     * callable factory will be invoked and the instance will be retained.
     *
     * @param string $class The class of the Mapper instance to retrieve.
     *
     * @return Mapper A Mapper instance.
     *
     * @throws Exception When an Mapper type is not found.
     *
     */
    public function get($class)
    {
        if (! isset($this->factories[$class])) {
            throw Exception::mapperNotFound($class);
        }

        if (! isset($this->instances[$class])) {
            $factory = $this->factories[$class];
            $this->instances[$class] = call_user_func($factory);
        }

        return $this->instances[$class];
    }
}
