<?php
namespace Atlas\Mapper;

use Atlas\Table\Row;
use Atlas\Table\RowSet;

class RecordFactory
{
    protected $recordClass;
    protected $recordSetClass;

    public function newRecord(Row $row, array $related)
    {
        $recordClass = $this->getRecordClass();
        return new $recordClass($row, $related);
    }

    public function getRecordClass()
    {
        if (! $this->recordClass) {
            // Foo\Bar\BazMapper -> Foo\Bar\BazRecord
            $class = substr(get_class($this), -6);
            $this->recordClass = "{$class}Record";
        }

        if (! class_exists($this->recordClass)) {
            $this->recordClass = 'Atlas\Mapper\Record';
        }

        return $this->recordClass;
    }

    public function newRecordSet(array $records = [])
    {
        $recordSetClass = $this->getRecordSetClass();
        return new $recordSetClass($records);
    }

    public function getRecordSetClass()
    {
        if (! $this->recordSetClass) {
            // Foo\Bar\BazMapper -> Foo\Bar\BazRecordSet
            $class = substr(get_class($this), -6);
            $this->recordSetClass = "{$class}RecordSet";
        }

        if (! class_exists($this->recordSetClass)) {
            $this->recordSetClass = 'Atlas\Mapper\RecordSet';
        }

        return $this->recordSetClass;
    }
}
