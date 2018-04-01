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

class Container extends \Atlas\Mapper\Container
{
    public function newAtlas($transactionClass = MiniTransaction::CLASS)
    {
        return new Atlas(
            $this->newMapperLocator(),
            new $transactionClass($this->getConnectionLocator())
        );
    }
}
