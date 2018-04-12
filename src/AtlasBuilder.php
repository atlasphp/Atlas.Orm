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
use Atlas\Mapper\MapperQueryFactory;
use Atlas\Pdo\ConnectionLocator;
use Atlas\Table\TableLocator;

class AtlasBuilder
{
    protected $connectionLocator;

    protected $factory;

    protected $transactionClass = Transaction::CLASS;

    public function __construct(...$args)
    {
        $this->connectionLocator = ConnectionLocator::new(...$args);
        $this->factory = function ($class) {
            return new $class();
        };
    }

    public function getConnectionLocator() : ConnectionLocator
    {
        return $this->connectionLocator;
    }

    public function setFactory(callable $factory) : void
    {
        $this->factory = $factory;
    }

    public function setTransactionClass(string $transactionClass) : void
    {
        $this->transactionClass = $transactionClass;
    }

    public function newAtlas()
    {
        $tableLocator = new TableLocator(
            $this->connectionLocator,
            new MapperQueryFactory(),
            $this->factory
        );

        $transactionClass = $this->transactionClass;
        return new Atlas(
            new MapperLocator($tableLocator, $this->factory),
            new $transactionClass($this->getConnectionLocator())
        );
    }
}
