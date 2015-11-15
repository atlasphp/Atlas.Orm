<?php
namespace Atlas\Orm;

use Atlas\Orm\Mapper\MapperFactory;
use Atlas\Orm\Mapper\MapperLocator;
use Atlas\Orm\Mapper\MapperRelations;
use Atlas\Orm\Table\IdentityMap;
use Atlas\Orm\Table\TableFactory;
use Atlas\Orm\Table\TableLocator;
use Aura\Sql\ConnectionLocator;
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
    protected $tableLocator;

    public function __construct($db, $common = null)
    {
        $this->queryFactory = new QueryFactory($db, $common);
        $this->connectionLocator = new ConnectionLocator();
        $this->tableLocator = new TableLocator();
        $this->mapperLocator = new MapperLocator();
        $this->identityMap = new IdentityMap();
        $this->atlas = new Atlas(
            $this->mapperLocator,
            new Transaction($this->mapperLocator)
        );
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

    public function getTable($tableClass)
    {
        return $this->tableLocator->get($tableClass);
    }

    public function setDefaultConnection(callable $callable)
    {
        $this->connectionLocator->setDefault($callable);
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
        $this->setTable($tableClass);

        $mapperFactory = $this->newMapperFactory($mapperClass, $tableClass);
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

    public function setTable($tableClass)
    {
        if (! class_exists($tableClass)) {
            throw Exception::classDoesNotExist($tableClass);
        }

        if (! $this->tableLocator->has($tableClass)) {
            $this->tableLocator->set($tableClass, $this->newTableFactory($tableClass));
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

    public function newTableFactory($tableClass)
    {
        return new TableFactory($this, $tableClass);
    }
}
