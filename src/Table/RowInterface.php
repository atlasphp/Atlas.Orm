<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Orm\Table;

use Atlas\Orm\Exception;

/**
 *
 * __________
 *
 * @package atlas/orm
 *
 */
interface RowInterface
{
    public function has($col);

    public function getArrayCopy();

    public function getArrayDiff(array $init);

    public function hasStatus($status);

    public function getStatus();

    public function setStatus($status);
}
