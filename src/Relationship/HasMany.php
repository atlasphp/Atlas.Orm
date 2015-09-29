<?php
namespace Atlas\Relationship;

use Atlas\Mapper\Mapper;
use Atlas\Mapper\MapperLocator;
use Atlas\Table\Row;
use Atlas\Table\RowSet;

class HasMany extends AbstractRelationship
{
    public function fetchForRow(
        Row $nativeRow,
        array &$related,
        callable $custom = null
    ) {
        $this->fix();
        $foreignVal = $nativeRow->{$this->nativeCol};
        $foreign = $this->foreignSelect($foreignVal, $custom)->fetchRecordSet();
        $related[$this->name] = $foreign;
    }

    public function fetchForRowSet(
        RowSet $nativeRowSet,
        array &$relatedSet,
        callable $custom = null
    ) {
        $this->fix();

        $foreignVals = $this->getUniqueVals($nativeRowSet, $this->nativeCol);
        $foreignRecordSets = $this->groupRecordSets(
            $this->foreignSelect($foreignVals, $custom)->fetchRecordSet(),
            $this->foreignCol
        );

        foreach ($nativeRowSet as $nativeRow) {
            $key = $nativeRow->{$this->nativeCol};
            if (isset($foreignRecordSets[$key])) {
                $foreignRecordSet = $foreignRecordSets[$key];
            } else {
                $foreignRecordSet = $this->getMissing();
            }
            $relatedSet[$nativeRow->getPrimaryVal()][$this->name] = $foreignRecordSet;
        }
    }

    protected function getMissing()
    {
        if ($this->orNone) {
            return array();
        }

        return $this->foreignMapper->newRecordSet();
    }
}
