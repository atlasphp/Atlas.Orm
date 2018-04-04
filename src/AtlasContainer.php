<?php
declare(strict_types=1);

/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Orm;

use Atlas\Mapper\MapperLocator;
use Atlas\Pdo\ConnectionLocator;
use Atlas\Query\QueryFactory;
use Atlas\Table\TableLocator;

class AtlasContainer
{
    protected $connectionLocator;

    protected $factory;

    public function __construct(...$args)
    {
        $this->connectionLocator = ConnectionLocator::new(...$args);
        $this->factory = function ($class) {
            return new $class();
        };
    }

    public function getConnectionLocator()
    {
        return $this->connectionLocator;
    }

    public function setFactory(callable $factory)
    {
        $this->factory = $factory;
    }

    public function newAtlas($transactionClass = MiniTransaction::CLASS)
    {
        $tableLocator = new TableLocator(
            $this->connectionLocator,
            new QueryFactory(),
            $this->factory
        );

        return new Atlas(
            new MapperLocator($tableLocator, $this->factory),
            new $transactionClass($this->getConnectionLocator())
        );
    }
}
