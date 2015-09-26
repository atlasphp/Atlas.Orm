<?php
namespace Atlas\Relationship;

use Atlas\Atlas;
use Atlas\Mapper\Record;
use Atlas\Mapper\RecordSet;

class OneToMany extends AbstractRelationship
{
    public function stitchIntoRecord(
        Atlas $atlas,
        Record $record,
        callable $custom = null
    ) {
        $this->fix($atlas);
        $colsVals = [$this->foreignCol => $record->{$this->nativeCol}];
        $select = $atlas->select($this->foreignMapperClass, $colsVals, $custom);
        $record->{$this->field} = $select->fetchRecordSet();
    }

    public function stitchIntoRecordSet(
        Atlas $atlas,
        RecordSet $recordSet,
        callable $custom = null
    ) {
        $this->fix($atlas);

        $foreignColVals = array();
        foreach ($recordSet as $record) {
            $foreignColVals[] = $record->{$this->nativeCol};
        }
        array_unique($foreignColVals);

        $colsVals = [$this->foreignCol => $foreignColVals];
        $select = $atlas->select($this->foreignMapperClass, $colsVals, $custom);
        $related = $this->foreignMapper->fetchRecordsBySelect(
            $select,
            $this->foreignCol,
            $custom
        );

        foreach ($rows as &$row) {
            $key = $record->{$this->nativeCol};
            $record->{$this->field} = $related[$key][0];
        }
    }
}
