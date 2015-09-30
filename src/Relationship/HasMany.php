<?php
namespace Atlas\Relationship;

use Atlas\Mapper\Related;
use Atlas\Mapper\RelatedSet;
use Atlas\Table\Row;
use Atlas\Table\RowSet;

class HasMany extends AbstractRelationship
{
    public function fetchForRow(
        Row $nativeRow,
        Related $related,
        callable $custom = null
    ) {
        $this->fix();
        $foreignVal = $nativeRow->{$this->nativeCol};
        $foreignRecordSet = $this->foreignSelect($foreignVal, $custom)->fetchRecordSet();
        $related->{$this->name} = $foreignRecordSet;
    }

    public function fetchForRowSet(
        RowSet $nativeRowSet,
        RelatedSet $relatedSet,
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
            $related = $relatedSet->get($nativeRow->getPrimaryVal());
            $related->{$this->name} = $foreignRecordSet;
        }
    }

    protected function getMissing()
    {
        if ($this->orNone) {
            return array();
        }

        return $this->foreignMapper->newRecordSet();
    }

    protected function fixEmptyValue()
    {
        $this->emptyValue = [];
    }

    protected function fixForeignClass()
    {
        $this->foreignClass = $this->foreignMapper->getRecordSetClass();
    }
}
