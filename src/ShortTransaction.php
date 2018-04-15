<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
declare(strict_types=1);

namespace Atlas\Orm;

use Atlas\Mapper\Mapper;
use Atlas\Mapper\Record;

/**
 * Auto-begins a transaction on write, but does not commit or roll back.
 */
class ShortTransaction extends Transaction
{
    public function write(Mapper $mapper, string $method, Record $record) : void
    {
        $this->beginTransaction();
        $this->connectionLocator->lockToWrite();
        $mapper->$method($record);
    }
}