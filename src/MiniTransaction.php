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

class MiniTransaction extends TransactionStrategy
{
    public function write(Mapper $mapper, string $method, Record $record)
    {
        $this->connectionLocator->lockToWrite();

        $connection = $this->connectionLocator->getWrite();

        try {
            $connection->beginTransaction();
            $result = $mapper->$method($record);
            $connection->commit();
            return $result;
        } catch (\Exception $e) {
            $connection->rollBack();
            throw Exception::Exception($e->getCode(), $e->getMessage(), $e);
        }
    }
}
