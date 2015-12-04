<?php
namespace Atlas\Orm\Mapper;

use Atlas\Orm\Exception;
use Atlas\Orm\Table\Row;
use Atlas\Orm\Table\RowSet;

class RecordFactory
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

    public function newRecordFromRow(Row $row, array $relatedFields)
    {
        $recordClass = $this->getRecordClass();
        return new $recordClass($row, new Related($relatedFields));
    }

    public function newRecordSetFromRowSet(RowSet $rowSet, array $relatedFields)
    {
        $records = [];
        foreach ($rowSet as $row) {
            $records[] = $this->newRecordFromRow($row, $relatedFields);
        }
        return $this->newRecordSet($records);
    }

    public function newRecordSet(array $records = [])
    {
        $recordSetClass = $this->getRecordSetClass();
        return new $recordSetClass($records);
    }
}
