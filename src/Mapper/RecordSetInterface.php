<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @package atlas/orm
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Orm\Mapper;

use ArrayAccess;
use Countable;
use IteratorAggregate;

interface RecordSetInterface extends ArrayAccess, Countable, IteratorAggregate
{
    public function isEmpty();

    public function getArrayCopy();
}
