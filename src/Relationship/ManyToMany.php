<?php
namespace Atlas\Relationship;

use Atlas\Mapper\Mapper;
use Atlas\Mapper\MapperLocator;
use Atlas\Table\Row;
use Atlas\Table\RowSet;

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

    protected function fixThroughNativeCol(MapperLocator $mapperLocator)
    {
        if ($this->throughNativeCol) {
            return;
        }

        $nativeMapper = $mapperLocator->get($this->nativeMapperClass);
        $this->throughNativeCol = $nativeMapper->getTable()->getPrimary();
    }

    protected function fixThroughForeignCol(MapperLocator $mapperLocator)
    {
        if ($this->throughForeignCol) {
            return;
        }

        $foreignMapper = $mapperLocator->get($this->foreignMapperClass);
        $this->throughForeignCol = $foreignMapper->getTable()->getPrimary();
    }

    protected function fixForeignCol(MapperLocator $mapperLocator)
    {
        if ($this->foreignCol) {
            return;
        }

        $foreignMapper = $mapperLocator->get($this->foreignMapperClass);
        $this->foreignCol = $foreignMapper->getTable()->getPrimary();
    }

    public function fetchForRow(
        MapperLocator $mapperLocator,
        Row $row,
        array &$related,
        callable $custom = null
    ) {
        $this->fix($mapperLocator);
        $throughRecordSet = $related[$this->throughRelated];
        $foreignVals = $this->getUniqueVals($throughRecordSet, $this->throughForeignCol);
        $foreign = $this->fetchForeignRecordSet($foreignVals, $custom);
        $related[$this->field] = $foreign;
    }

    public function fetchForRowSet(
        MapperLocator $mapperLocator,
        RowSet $rowSet,
        array &$relatedSet,
        callable $custom = null
    ) {
        $this->fix($mapperLocator);

        $foreignColVals = [];
        foreach ($rowSet as $row) {
            $primaryVal = $row->getPrimaryVal();
            $throughRecordSet = $relatedSet[$primaryVal][$this->throughRelated];
            $foreignColVals = array_merge(
                $foreignColVals,
                $this->getUniqueVals($throughRecordSet, $this->throughForeignCol)
            );
        }
        $foreignColVals = array_unique($foreignColVals);

        $colsVals = [$this->foreignCol => $foreignColVals];
        $foreignRecordSet = $this->fetchForeignRecordSet($colsVals, $custom);

        foreach ($rowSet as $row) {
            $primaryVal = $row->getPrimaryVal();
            $throughRecordSet = $relatedSet[$primaryVal][$this->throughRelated];
            $vals = $this->getUniqueVals($throughRecordSet, $this->throughForeignCol);
            $relatedSet[$primaryVal][$this->field] = $foreignRecordSet->newRecordSetBy(
                $this->foreignCol,
                $vals
            );
        }
    }
}
