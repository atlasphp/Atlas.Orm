<?php
namespace Atlas\Relationship;

use Atlas\Atlas;
use Atlas\Mapper\Record;
use Atlas\Mapper\RecordSet;

class ManyToMany extends AbstractRelationship
{
    public function throughNativeCol($throughNativeCol)
    {
        $this->throughNativeCol = $throughNativeCol;
        return $this;
    }

    public function throughForeignCol($throughForeignCol)
    {
        $this->throughForeignCol = $throughForeignCol;
        return $this;
    }

    protected function fixThroughNativeCol(Atlas $atlas)
    {
        if ($this->throughNativeCol) {
            return;
        }

        $nativeMapper = $atlas->mapper($this->nativeMapperClass);
        $this->throughNativeCol = $nativeMapper->getTable()->getPrimary();
    }

    protected function fixThroughForeignCol(Atlas $atlas)
    {
        if ($this->throughForeignCol) {
            return;
        }

        $foreignMapper = $atlas->mapper($this->foreignMapperClass);
        $this->throughForeignCol = $foreignMapper->getTable()->getPrimary();
    }

    protected function fixForeignCol(Atlas $atlas)
    {
        if ($this->foreignCol) {
            return;
        }

        $foreignMapper = $atlas->mapper($this->foreignMapperClass);
        $this->foreignCol = $foreignMapper->getTable()->getPrimary();
    }

    public function stitchIntoRecord(
        Atlas $atlas,
        Record $record,
        callable $custom = null
    ) {
        $this->fix($atlas);
        $throughRecordSet = $record->{$this->throughField};
        $foreignColVals = $throughRecordSet->getUniqueVals($this->throughForeignCol);
        $colsVals = [$this->foreignCol => $foreignColVals];
        $select = $atlas->select($this->foreignMapperClass, $colsVals, $custom);
        $record->{$this->field} = $select->fetchRecordSet();
    }

    public function stitchIntoRecordSet(
        Atlas $atlas,
        RecordSet $recordSet,
        callable $custom = null
    ) {
        $this->fix($atlas);

        $foreignColVals = [];
        foreach ($recordSet as $record) {
            $throughRecordSet = $record->{$this->throughField};
            $foreignColVals = array_merge(
                $foreignColVals,
                $throughRecordSet->getUniqueVals($this->throughForeignCol)
            );
        }
        $foreignColVals = array_unique($foreignColVals);

        $colsVals = [$this->foreignCol => $foreignColVals];
        $select = $atlas->select($this->foreignMapperClass, $colsVals, $custom);
        $foreignRecordSet = $select->fetchRecordSet();

        foreach ($recordSet as $record) {
            $throughRecordSet = $record->{$this->throughField};
            $vals = $throughRecordSet->getUniqueVals($this->throughForeignCol);
            $record->{$this->field} = $foreignRecordSet->newRecordSetBy(
                $this->foreignCol,
                $vals
            );
        }
    }
}
