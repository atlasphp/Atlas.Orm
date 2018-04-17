<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
declare(strict_types=1);

namespace Atlas\Orm\Transaction;

use Atlas\Pdo\ConnectionLocator;
use Atlas\Mapper\Mapper;
use Atlas\Mapper\Record;

abstract class Transaction
{
    protected $connectionLocator;

    public function __construct(ConnectionLocator $connectionLocator)
    {
        $this->connectionLocator = $connectionLocator;
    }

    abstract public function read(Mapper $mapper, string $method, array $params);

    abstract public function write(Mapper $mapper, string $method, Record $record) : void;

    public function beginTransaction() : void
    {
        foreach ($this->getConnections() as $connection) {
            if (! $connection->inTransaction()) {
                $connection->beginTransaction();
            }
        }
    }

    public function commit() : void
    {
        foreach ($this->getConnections() as $connection) {
            if ($connection->inTransaction()) {
                $connection->commit();
            }
        }
    }

    public function rollBack() : void
    {
        foreach ($this->getConnections() as $connection) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }
        }
    }

    protected function getConnections() : array
    {
        return [
            $this->connectionLocator->getRead(),
            $this->connectionLocator->getWrite(),
        ];
    }
}
