<?php
namespace Atlas\Orm\Mapper;

use Atlas\Orm\Exception;
use Atlas\Orm\Table\AbstractRow;

abstract class AbstractRecordFactory
{
    public function getRecordClass()
    {
        static $recordClass;
        if (! $recordClass) {
            $recordClass = substr(get_class($this), 0, -7);
        }
        return $recordClass;
    }

    public function getRecordSetClass()
    {
        static $recordSetClass;
        if (! $recordSetClass) {
            $recordSetClass = $this->getRecordClass() . 'Set';
        }
        return $recordSetClass;
    }

    // row can be array or Row object
    public function newRecord(AbstractRow $row, array $relatedFields)
    {
        $recordClass = $this->getRecordClass();
        return new $recordClass($row, new Related($relatedFields));
    }

    // rowSet can be array of Rows, or RowSet object
    public function newRecordSetFromRows($rows, array $relatedFields)
    {
        $records = [];
        foreach ($rows as $row) {
            $records[] = $this->newRecord($row, $relatedFields);
        }
        return $this->newRecordSet($records);
    }

    public function newRecordSet(array $records = [])
    {
        $recordSetClass = $this->getRecordSetClass();
        return new $recordSetClass($this, $records);
    }

    public function assertRecordClass($record)
    {
        static $recordClass;
        if (! $recordClass) {
            $recordClass = $this->getRecordClass();
        }

        if (! is_object($record)) {
            throw Exception::invalidType($recordClass, gettype($record));
        }

        if (! $record instanceof $recordClass) {
            throw Exception::invalidType($recordClass, $record);
        }
    }
}
