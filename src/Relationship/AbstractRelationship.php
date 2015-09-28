<?php
namespace Atlas\Relationship;

use Atlas\Mapper\Mapper;
use Atlas\Mapper\MapperLocator;
use Atlas\Table\Row;
use Atlas\Table\RowSet;

abstract class AbstractRelationship
{
    protected $mapperLocator;

    protected $name;

    protected $nativeMapperClass;
    protected $nativeMapper;

    protected $foreignMapperClass;
    protected $forignMapper;

    protected $nativeCol;
    protected $throughNativeCol;
    protected $throughForeignCol;
    protected $foreignCol;

    protected $fixed = false;

    public function __construct(
        MapperLocator $mapperLocator,
        $nativeMapperClass,
        $name,
        $foreignMapperClass,
        $throughName = null
    ) {
        $this->mapperLocator = $mapperLocator;
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

    protected function fix()
    {
        if ($this->fixed) {
            return;
        }

        $this->nativeMapper = $this->mapperLocator->get($this->nativeMapperClass);
        $this->foreignMapper = $this->mapperLocator->get($this->foreignMapperClass);

        $this->fixNativeCol();
        $this->fixThroughNativeCol();
        $this->fixThroughForeignCol();
        $this->fixForeignCol();

        $this->fixed = true;
    }

    protected function fixNativeCol()
    {
        if ($this->nativeCol) {
            return;
        }

        $this->nativeCol = $this->nativeMapper->getTable()->getPrimary();
    }

    protected function fixForeignCol()
    {
        if ($this->foreignCol) {
            return;
        }

        $this->foreignCol = $this->nativeMapper->getTable()->getPrimary();
    }

    protected function fixThroughNativeCol()
    {
    }

    protected function fixThroughForeignCol()
    {
    }

    protected function foreignSelect($foreignVal, callable $custom = null)
    {
        $select = $this->foreignMapper->select([$this->foreignCol => $foreignVal]);
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
        Row $row,
        array &$related,
        callable $custom = null
    );

    abstract public function fetchForRowSet(
        RowSet $row,
        array &$relatedSet,
        callable $custom = null
    );
}
