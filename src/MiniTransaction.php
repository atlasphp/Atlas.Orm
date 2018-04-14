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
use Exception;

/**
 * Auto-begins, and then commits or rolls back, each write operation.
 */
class MiniTransaction extends Transaction
{
    public function write(Mapper $mapper, string $method, Record $record) : void
    {
        $this->connectionLocator->lockToWrite();
        try {
            $this->beginTransaction();
            $mapper->$method($record);
            $this->commit();
        } catch (Exception $e) {
            $this->rollBack();
            $c = get_class($e);
            throw new $c($e->getMessage(), $e->getCode(), $e);
        }
    }
}
