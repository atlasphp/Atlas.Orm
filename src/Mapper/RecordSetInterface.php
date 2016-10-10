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
 * An interface for RecordSet objects.
 *
 * @package atlas/orm
 *
 */
interface RecordSetInterface extends ArrayAccess, Countable, IteratorAggregate
{
    /**
     *
     * Is the RecordSet empty?
     *
     * @return bool
     *
     */
    public function isEmpty();

    /**
     *
     * Returns an array copy of the Record objects in the RecordSet.
     *
     * @return array
     *
     */
    public function getArrayCopy();
}
