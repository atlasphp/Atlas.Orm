<?php
namespace Atlas\Relationship;

use Atlas\Mapper\Mapper;
use Atlas\Mapper\MapperLocator;
use Atlas\Table\Row;
use Atlas\Table\RowSet;

class OneToMany extends AbstractRelationship
{
    public function fetchForRow(
        MapperLocator $mapperLocator,
        Row $row,
        array &$related,
        callable $custom = null
    ) {
        $this->fix($mapperLocator);
        $foreignVal = $row[$this->nativeCol];
        $foreign = $this->fetchForeignRecordSet($foreignVal, $custom);
        $related[$this->field] = $foreign;
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
                $foreign = $foreignGroups[$key];
            }
            $relatedSet[$row->getPrimaryVal()][$this->field] = $foreign;
        }
    }
}
