<?php
namespace Atlas\Relation;

use Atlas\Atlas;

class OneToOne extends AbstractRelationship
{
    public function stitchIntoOne(
        Atlas $atlas,
        Record $record,
        callable $custom = null
    ) {
        $this->fix($atlas);

        $record->{$this->field} = $this->foreignMapper->fetchRecordBy(
            [$this->foreignCol => $record->{$this->nativeCol}],
            $custom
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
            $foreignColVals[] = $record->{$this->nativeCol};
        }
        array_unique($foreignColVals);

        $related = $this->foreignMapper->fetchEntitiesBy(
            [$this->foreignCol => $foreignColVals],
            $this->foreignCol,
            $custom
        );

        foreach ($rows as &$row) {
            $key = $record->{$this->nativeCol};
            $record->{$this->field} = $related[$key];
        }
    }
}
