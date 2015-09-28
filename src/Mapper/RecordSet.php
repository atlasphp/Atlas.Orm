<?php
namespace Atlas\Mapper;

use ArrayObject;

class RecordSet extends ArrayObject
{
    public function isEmpty()
    {
        return count($this) == 0;
    }

    public function getArrayCopy()
    {
        $array = [];
        foreach ($this as $key => $record) {
            $array[$key] = $record->getArrayCopy();
        }
        return $array;
    }
}
