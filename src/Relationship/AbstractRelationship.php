<?php
namespace Atlas\Relationship;

use Atlas\Mapper\Mapper;
use Atlas\Mapper\MapperLocator;
use Atlas\Table\Row;
use Atlas\Table\RowSet;

abstract class AbstractRelationship
{
    protected $name;

    protected $nativeMapperClass;
    protected $foreignMapperClass;

    protected $nativeCol;
    protected $throughNativeCol;
    protected $throughForeignCol;
    protected $foreignCol;

    protected $fixed = false;

    public function __construct($nativeMapperClass, $name, $foreignMapperClass, $throughName = null)
    {
        $this->nativeMapperClass = $nativeMapperClass;
        $this->name = $name;
        $this->foreignMapperClass = $foreignMapperClass;
        $this->throughName = $throughName;
    }

    public function nativeCol($nativeCol)
    {
        $this->nativeCol = $nativeCol;
        return $this;
    }

    public function foreignCol($foreignCol)
    {
        $this->foreignCol = $foreignCol;
        return $this;
    }

    protected function fix(MapperLocator $mapperLocator)
    {
        if ($this->fixed) {
            return;
        }
        $this->fixNativeCol($mapperLocator);
        $this->fixThroughNativeCol($mapperLocator);
        $this->fixThroughForeignCol($mapperLocator);
        $this->fixForeignCol($mapperLocator);
        $this->fixed = true;
    }

    protected function fixNativeCol(MapperLocator $mapperLocator)
    {
        if ($this->nativeCol) {
            return;
        }

        $nativeMapper = $mapperLocator->get($this->nativeMapperClass);
        $this->nativeCol = $nativeMapper->getTable()->getPrimary();
    }

    protected function fixForeignCol(MapperLocator $mapperLocator)
    {
        if ($this->foreignCol) {
            return;
        }

        $nativeMapper = $mapperLocator->get($this->nativeMapperClass);
        $this->foreignCol = $nativeMapper->getTable()->getPrimary();
    }

    protected function fixThroughNativeCol(MapperLocator $mapperLocator)
    {
    }

    protected function fixThroughForeignCol(MapperLocator $mapperLocator)
    {
    }

    protected function fetchForeignRecord($foreignVal, callable $custom = null)
    {
        $foreignMapper = $this->mapperLocator->get($this->foreignMapperClass);
        return $foreignMapper->fetchRecordBySelect($this->foreignSelect(
            $foreignMapper,
            $foreignVal,
            $custom
        ));
    }

    protected function fetchForeignRecordSet($foreignVal, callable $custom = null)
    {
        $foreignMapper = $this->mapperLocator->get($this->foreignMapperClass);
        return $foreignMapper->fetchRecordSetBySelect($this->foreignSelect(
            $foreignMapper,
            $foreignVal,
            $custom
        ));
    }

    protected function foreignSelect(Mapper $foreignMapper, $foreignVal, callable $custom = null)
    {
        $select = $foreignMapper->select([$this->foreignCol => $foreignVal]);
        if ($custom) {
            $custom($select);
        }
        return $select;
    }

    protected function getUniqueVals($set, $col)
    {
        $vals = [];
        foreach ($set as $item) {
            $vals[] = $item->$col;
        }
        return array_unique($vals);
    }

    abstract public function fetchForRow(
        MapperLocator $mapperLocator,
        Row $row,
        array &$related,
        callable $custom = null
    );

    abstract public function fetchForRowSet(
        MapperLocator $mapperLocator,
        RowSet $row,
        array &$relatedSet,
        callable $custom = null
    );
}
