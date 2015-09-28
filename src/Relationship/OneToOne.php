<?php
namespace Atlas\Relationship;

use Atlas\Mapper\Mapper;
use Atlas\Mapper\MapperLocator;
use Atlas\Table\Row;
use Atlas\Table\RowSet;

class OneToOne extends AbstractRelationship
{
    public function fetchForRow(
        MapperLocator $mapperLocator,
        Row $row,
        array &$related,
        callable $custom = null
    ) {
        $this->fix($mapperLocator);
        $foreignVal = $row[$this->nativeCol];
        $foreign = $this->fetchForeignRecord($foreignVal, $custom);
        $related[$this->name] = $foreign;
    }

    public function fetchForRowSet(
        MapperLocator $mapperLocator,
        RowSet $rowSet,
        array &$relatedSet,
        callable $custom = null
    ) {
        $this->fix($mapperLocator);

        $foreignVals = $this->getUniqueVals($rowSet, $this->nativeCol);
        $foreignRecordSet = $this->fetchForeignRecordSet($foreignVals, $custom);

        $foreignGroups = array();
        if ($foreignRecordSet) {
            $foreignGroups = $foreignRecordSet->getGroupsBy($this->foreignCol);
        }

        foreach ($rowSet as $row) {
            $foreign = false;
            $key = $row[$this->nativeCol];
            if (isset($foreignGroups[$key])) {
                $foreign = $foreignGroups[$key][0];
            }
            $relatedSet[$row->getPrimaryVal()][$this->name] = $foreign;
        }
    }
}
