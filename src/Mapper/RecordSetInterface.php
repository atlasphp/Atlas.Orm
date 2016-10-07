<?php
namespace Atlas\Orm\Mapper;

use ArrayAccess;
use Countable;
use IteratorAggregate;

interface RecordSetInterface extends ArrayAccess, Countable, IteratorAggregate
{
    public function isEmpty();

    public function getArrayCopy();
}
