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

use Atlas\Pdo\ConnectionLocator;
use Atlas\Mapper\Mapper;
use Atlas\Mapper\Record;

abstract class TransactionStrategy
{
    protected $connectionLocator;

    public function __construct(ConnectionLocator $connectionLocator)
    {
        $this->connectionLocator = $connectionLocator;
    }

    public function read(Mapper $mapper, string $method, array $params)
    {
        return $mapper->$method(...$params);
    }

    abstract public function write(Mapper $mapper, string $method, Record $record);

    public function commit()
    {
        // do nothing
    }

    public function rollBack()
    {
        // do nothing
    }
}
