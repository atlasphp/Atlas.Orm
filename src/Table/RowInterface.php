<?php
namespace Atlas\Orm\Table;

use Atlas\Orm\Exception;

interface RowInterface
{
    public function has($col);

    public function getArrayCopy();

    public function getArrayDiff(array $init);

    public function getPrimary();

    public function hasStatus($status);

    public function getStatus();

    public function setStatus($status);
}
