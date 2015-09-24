<?php
namespace Atlas\Relation;

use Atlas\Atlas;

class ManyToMany extends AbstractRelationship
{
    protected function fixThroughNativeCol()
    {
        if (! $this->throughNativeCol) {
            $this->throughNativeCol = $this->nativeMapper->getTable()->getPrimary();
        }
    }

    protected function fixThroughForeignCol()
    {
        if (! $this->throughForeignCol) {
            $this->throughForeignCol = $this->foreignMapper->getTable()->getPrimary();
        }
    }

    public function stitchIntoOne(
        Atlas $atlas,
        Record $record,
        callable $custom = null
    ) {
        $this->fix($atlas);

        $foreignColVals = array();
        foreach ($row[$this->throughField] as $entity) {
            $foreignColVals[] = $entity->{$this->throughField};
        }
        array_unique($foreignColVals);

        $select = $this->foreignMapper->select($colsVals);
        if ($custom) {
            $custom($select);
        }

        $record->{$this->field} = $this->foreignMapper->fetchRecordSetBySelect(
            $select
        );
    }

    public function stitchIntoMany(
        Atlas $atlas,
        RecordSet $recordSet,
        callable $custom = null
    ) {
        $this->fix($atlas);

        $foreignColVals = array();
        foreach ($rows as $row) {
            foreach ($row[$this->through] as $entity) {
                $foreignColVals[] = $entity->{$this->throughField};
            }
        }
        array_unique($foreignColVals);

        $select = $this->foreignMapper->select([$this->foreignCol => $foreignColVals]);
        if ($custom) {
            $custom($select);
        }

        $collections = $this->foreignMapper->fetchRecordSetsBySelect(
            $select,
            $this->foreignCol
        );

        foreach ($rows as &$row) {
            $record->{$this->field} = $collections[$row->{$this->nativeCol}];
        }
    }
}
