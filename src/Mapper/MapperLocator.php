<?php
/**
 *
 * This file is part of the Aura Project for PHP.
 *
 * @package Atlas.Atlas
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
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
        $class = $this->getMapperClass($class);
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
        $class = $this->getMapperClass($class);

        if (! isset($this->factories[$class])) {
            throw Exception::mapperNotFound($class);
        }

        if (! isset($this->instances[$class])) {
            $factory = $this->factories[$class];
            $this->instances[$class] = call_user_func($factory);
        }

        return $this->instances[$class];
    }

    /** @todo class_exists($class) */
    /** @todo $class instanceof Mapper */
    protected function getMapperClass($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        if (substr($class, -6) == 'Record') {
            $class = substr($class, 0, -6) . 'Mapper';
        }

        if (substr($class, -9) == 'RecordSet') {
            $class = substr($class, 0, -9) . 'Mapper';
        }

        return $class;
    }
}
