<?php
namespace Atlas\Relationship;

use Atlas\Atlas;
use Atlas\Mapper\Record;
use Atlas\Mapper\RecordSet;

class OneToOne extends AbstractRelationship
{
    public function stitchIntoRecord(
        Atlas $atlas,
        Record $record,
        callable $custom = null
    ) {
        $this->fix($atlas);
        $colsVals = [$this->foreignCol => $record->{$this->nativeCol}];
        $select = $atlas->select($this->foreignMapperClass, $colsVals, $custom);
        $record->{$this->field} = $select->fetchRecord();
    }

    public function stitchIntoRecordSet(
        Atlas $atlas,
        RecordSet $recordSet,
        callable $custom = null
    ) {
        $this->fix($atlas);

        $colsVals = [$this->foreignCol => $recordSet->getUniqueVals($this->nativeCol)];
        $select = $atlas->select($this->foreignMapperClass, $colsVals, $custom);
        $foreignRecordSet = $select->fetchRecordSet();

        $foreignGroups = array();
        if ($foreignRecordSet) {
            $foreignGroups = $foreignRecordSet->getGroupsBy($this->foreignCol);
        }

        foreach ($recordSet as $record) {
            $record->{$this->field} = false;
            $key = $record->{$this->nativeCol};
            if (isset($foreignGroups[$key])) {
                $record->{$this->field} = $foreignGroups[$key][0];
            }
        }
    }
}
