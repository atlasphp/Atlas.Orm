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

use Atlas\Mapper\Mapper;
use Atlas\Mapper\Record;

class LongTransaction extends TransactionStrategy
{
    public function read(Mapper $mapper, string $method, array $params)
    {
        foreach ($this->getConnections() as $connection) {
            if (! $connection->inTransaction()) {
                $connection->beginTransaction();
            }
        }

        return $mapper->$method(...$params);
    }

    public function write(Mapper $mapper, string $method, Record $record)
    {
        foreach ($this->getConnections() as $connection) {
            if (! $connection->inTransaction()) {
                $connection->beginTransaction();
            }
        }

        $this->connectionLocator->lockToWrite();
        return $mapper->$method($record);
    }

    public function commit()
    {
        foreach ($this->getConnections() as $connection) {
            if ($connection->inTransaction()) {
                $connection->commit();
            }
        }
    }

    public function rollBack()
    {
        foreach ($this->getConnections() as $connection) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }
        }
    }

    protected function getConnections()
    {
        return [
            $this->connectionLocator->getRead(),
            $this->connectionLocator->getWrite(),
        ];
    }
}
