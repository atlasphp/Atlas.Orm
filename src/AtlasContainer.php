<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Orm;

use Atlas\Orm\Mapper\MapperLocator;
use Atlas\Orm\Mapper\MapperEvents;
use Atlas\Orm\Relationship\Relationships;
use Atlas\Orm\Table\TableEvents;
use Atlas\Orm\Table\TableLocator;
use Atlas\Orm\Table\IdentityMap;
use Aura\Sql\ConnectionLocator;
use Aura\Sql\ExtendedPdo;
use Aura\SqlQuery\QueryFactory;

/**
 *
 * __________
 *
 * @package atlas/orm
 *
 */
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
        $this->tableLocator = new TableLocator();
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
        $this->setTable($tableClass);

        $eventsClass = $mapperClass . 'Events';
        $eventsClass = class_exists($eventsClass)
            ? $eventsClass
            : MapperEvents::CLASS;

        $mapperFactory = function () use ($mapperClass, $tableClass, $eventsClass) {
            return new $mapperClass(
                $this->tableLocator->get($tableClass),
                new Relationships($this->getMapperLocator()),
                $this->newInstance($eventsClass)
            );
        };

        $this->mapperLocator->set($mapperClass, $mapperFactory);
    }

    protected function setTable($tableClass)
    {
        $eventsClass = $tableClass . 'Events';
        $eventsClass = class_exists($eventsClass)
            ? $eventsClass
            : TableEvents::CLASS;
        $factory = function () use ($tableClass, $eventsClass) {
            return new $tableClass(
                $this->connectionLocator,
                $this->queryFactory,
                new IdentityMap(),
                $this->newInstance($eventsClass)
            );
        };

        $this->tableLocator->set($tableClass, $factory);
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
