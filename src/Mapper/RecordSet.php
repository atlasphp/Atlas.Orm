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

    public function getUniqueVals($field)
    {
        $vals = [];
        foreach ($this as $record) {
            $vals[] = $record->{$field};
        }
        return array_unique($vals);
    }

    public function getGroupsBy($field)
    {
        $groups = array();
        foreach ($this as $record) {
            $key = $record->$field;
            if (! isset($groups[$key])) {
                $groups[$key] = new self([]);
            }
            $groups[$key][] = $record;
        }
        return $groups;
    }

    public function newRecordSetBy($field, $vals)
    {
        $vals = (array) $vals;
        $records = [];
        foreach ($this as $record) {
            if (in_array($record->$field, $vals)) {
                $records[] = $record;
            }
        }

        if ($records) {
            return new self($records);
        }

        return $records;
    }
}
