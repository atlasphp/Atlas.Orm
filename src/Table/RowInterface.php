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
namespace Atlas\Orm\Table;

use Atlas\Orm\Exception;

interface RowInterface
{
    public function has($col);

    public function getArrayCopy();

    public function getArrayDiff(array $init);

    public function hasStatus($status);

    public function getStatus();

    public function setStatus($status);
}
