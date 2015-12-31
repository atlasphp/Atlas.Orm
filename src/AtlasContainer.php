<?php
namespace Atlas\Orm;

use Atlas\Orm\Mapper\MapperLocator;
use Atlas\Orm\Mapper\Plugin;
use Atlas\Orm\Relationship\Relationships;
use Atlas\Orm\Table\Gateway;
use Atlas\Orm\Table\GatewayLocator;
use Atlas\Orm\Table\IdentityMap;
use Aura\Sql\ConnectionLocator;
use Aura\Sql\ExtendedPdo;
use Aura\SqlQuery\QueryFactory;

class AtlasContainer
{
    protected $atlas;
    protected $connectionLocator;
    protected $factories;
    protected $mapperLocator;
    protected $queryFactory;

    public function __construct(
        $dsn,
        $username = null,
        $password = null,
        array $options = [],
        array $attributes = []
    ) {
        $this->setConnectionLocator(func_get_args());
        $this->setQueryFactory($dsn);
        $this->gatewayLocator = new GatewayLocator();
        $this->mapperLocator = new MapperLocator();
        $this->atlas = new Atlas(
            $this->mapperLocator,
            new Transaction($this->mapperLocator)
        );
    }

    protected function setConnectionLocator(array $args)
    {
        $this->connectionLocator = new ConnectionLocator();
        $this->connectionLocator->setDefault(function () use ($args) {
            return new ExtendedPdo(...$args);
        });
    }

    public function getConnectionLocator()
    {
        return $this->connectionLocator;
    }

    public function getMapperLocator()
    {
        return $this->mapperLocator;
    }

    protected function setQueryFactory($dsn)
    {
        $parts = explode(':', $dsn);
        $db = array_shift($parts);
        $this->queryFactory = new QueryFactory($db);
    }

    public function getAtlas()
    {
        return $this->atlas;
    }

    public function setReadConnection($name, callable $callable)
    {
        $this->connectionLocator->setRead($name, $callable);
    }

    public function setWriteConnection($name, callable $callable)
    {
        $this->connectionLocator->setWrite($name, $callable);
    }

    public function setMappers(array $mapperClasses)
    {
        foreach ($mapperClasses as $mapperClass) {
            $this->setMapper($mapperClass);
        }
    }

    public function setMapper($mapperClass)
    {
        if (! class_exists($mapperClass)) {
            throw Exception::classDoesNotExist($mapperClass);
        }

        $tableClass = $mapperClass::getTableClass();
        if (! class_exists($tableClass)) {
            throw Exception::classDoesNotExist($tableClass);
        }

        $this->setGateway($tableClass);

        $pluginClass = substr($mapperClass, 0, -6) . 'Plugin';
        $pluginClass = class_exists($pluginClass)
            ? $pluginClass
            : Plugin::CLASS;

        $mapperFactory = function () use ($mapperClass, $tableClass, $pluginClass) {
            return new $mapperClass(
                $this->connectionLocator,
                $this->queryFactory,
                $this->gatewayLocator->get($tableClass),
                $this->newInstance($pluginClass),
                new Relationships($this->getMapperLocator())
            );
        };

        $this->mapperLocator->set($mapperClass, $mapperFactory);
    }

    protected function setGateway($tableClass)
    {
        $gatewayClass = substr($tableClass, 0, -5) . 'Gateway';
        $gatewayClass = class_exists($gatewayClass)
            ? $gatewayClass
            : Gateway::CLASS;

        $factory = function () use ($gatewayClass, $tableClass) {
            return new $gatewayClass(
                new $tableClass(),
                new IdentityMap()
            );
        };

        $this->gatewayLocator->set($tableClass, $factory);
    }

    public function setFactoryFor($class, callable $callable)
    {
        $this->factories[$class] = $callable;
    }

    public function newInstance($class)
    {
        if (isset($this->factories[$class])) {
            $factory = $this->factories[$class];
            return $factory();
        }

        return new $class();
    }
}
