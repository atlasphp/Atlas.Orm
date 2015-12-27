<?php
namespace Atlas\Orm\Mapper;

interface RecordSetInterface
{
    public function offsetExists($offset);

    public function offsetGet($offset);

    public function offsetSet($offset, $value);

    public function offsetUnset($offset);

    public function count();

    public function getIterator();

    public function isEmpty();

    public function getArrayCopy();
}
