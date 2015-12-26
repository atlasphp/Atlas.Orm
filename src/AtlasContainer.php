<?php
namespace Atlas\Orm;

use Atlas\Orm\Mapper\MapperFactory;
use Atlas\Orm\Mapper\MapperLocator;
use Atlas\Orm\Mapper\MapperRelations;
use Atlas\Orm\Mapper\IdentityMap;
use Aura\Sql\ConnectionLocator;
use Aura\Sql\ExtendedPdo;
use Aura\SqlQuery\QueryFactory;
use ReflectionMethod;

class AtlasContainer
{
    protected $atlas;
    protected $connectionLocator;
    protected $factories;
    protected $identityMap;
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

        $this->mapperLocator = new MapperLocator();
        $this->identityMap = new IdentityMap();

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

    public function getConnectionLocator()
    {
        return $this->connectionLocator;
    }

    public function getQueryFactory()
    {
        return $this->queryFactory;
    }

    public function getMapperLocator()
    {
        return $this->mapperLocator;
    }

    public function getIdentityMap()
    {
        return $this->identityMap;
    }

    public function setReadConnection($name, callable $callable)
    {
        $this->connectionLocator->setRead($name, $callable);
    }

    public function setWriteConnection($name, callable $callable)
    {
        $this->connectionLocator->setWrite($name, $callable);
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

        $self = $this;
        $mapperFactory = function () use ($self, $mapperClass, $tableClass) {
            return new $mapperClass(
                $self->getConnectionLocator(),
                $self->getQueryFactory(),
                $self->getIdentityMap(),
                $self->newInstance($tableClass),
                $self->newInstance($mapperClass . 'Events'),
                $self->newMapperRelations()
            );
        };

        $this->mapperLocator->set($mapperClass, $mapperFactory);
    }

    public function setMappers(array $mapperClasses)
    {
        foreach ($mapperClasses as $key => $val) {
            if (is_int($key)) {
                $this->setMapper($val);
            } else {
                $this->setMapper($key, $val);
            }
        }
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

    public function newMapperFactory($mapperClass, $tableClass)
    {
        return new MapperFactory($this, $mapperClass, $tableClass);
    }

    public function newMapperRelations()
    {
        return new MapperRelations($this->getMapperLocator());
    }
}
