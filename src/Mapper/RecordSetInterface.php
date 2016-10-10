<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Orm\Mapper;

use ArrayAccess;
use Countable;
use IteratorAggregate;

/**
 *
 * __________
 *
 * @package atlas/orm
 *
 */
interface RecordSetInterface extends ArrayAccess, Countable, IteratorAggregate
{
    public function isEmpty();

    public function getArrayCopy();
}
