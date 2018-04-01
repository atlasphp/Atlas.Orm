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

class ShortTransaction extends TransactionStrategy
{
    public function write(Mapper $mapper, string $method, Record $record)
    {
        $this->connectionLocator->lockToWrite();

        $connection = $this->connectionLocator->getWrite();
        if (! $connection->inTransaction()) {
            $connection->beginTransaction();
        }

        return $mapper->$method($record);
    }

    public function commit()
    {
        $connection = $this->connectionLocator->getWrite();
        if ($connection->inTransaction()) {
            $connection->commit();
        }
    }

    public function rollBack()
    {
        $connection = $this->connectionLocator->getWrite();
        if ($connection->inTransaction()) {
            $connection->rollBack();
        }
    }
}
