<?php
namespace Atlas\Relationship;

use Atlas\Mapper\Mapper;
use Atlas\Mapper\MapperLocator;
use Atlas\Table\Row;
use Atlas\Table\RowSet;

class OneToOne extends AbstractRelationship
{
    public function fetchForRow(
        Row $row,
        array &$related,
        callable $custom = null
    ) {
        $this->fix();
        $foreignVal = $row->{$this->nativeCol};
        $foreign = $this->foreignSelect($foreignVal, $custom)->fetchRecord();
        $related[$this->name] = $foreign;
    }

    public function fetchForRowSet(
        RowSet $rowSet,
        array &$relatedSet,
        callable $custom = null
    ) {
        $this->fix();

        $foreignVals = $this->getUniqueVals($rowSet, $this->nativeCol);
        $foreignRecordSet = $this->foreignSelect($foreignVals, $custom)->fetchRecordSet();
        $foreignGroups = $this->getGroupsBy($foreignRecordSet, $this->foreignCol);

        foreach ($rowSet as $row) {
            $foreign = false;
            $key = $row->{$this->nativeCol};
            if (isset($foreignGroups[$key])) {
                $foreign = $foreignGroups[$key][0];
            }
            $relatedSet[$row->getPrimaryVal()][$this->name] = $foreign;
        }
    }
}
