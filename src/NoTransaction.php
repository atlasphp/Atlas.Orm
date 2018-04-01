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

class NoTransaction extends TransactionStrategy
{
    public function write(Mapper $mapper, string $method, Record $record)
    {
        $this->connectionLocator->lockToWrite();
        return $mapper->$method($record);
    }
}
