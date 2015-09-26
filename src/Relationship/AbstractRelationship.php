<?php
namespace Atlas\Relationship;

use Atlas\Atlas;
use Atlas\Mapper\Mapper;
use Atlas\Mapper\Record;
use Atlas\Mapper\RecordSet;

// the Relation should let you specify what to do when there are no
// related records. $rel->nullWhenEmpty(), arrayWhenEmpty(), newRecordWhenEmpty(),
// newRecordSetWhenEmpty()? or should it be a $rel->default() to indicate the
// default value? maybe Table should return "false" instead of "null" when no
// row is found.

abstract class AbstractRelationship
{
    protected $field;

    protected $nativeMapperClass;
    protected $foreignMapperClass;

    protected $nativeCol;
    protected $throughNativeCol;
    protected $throughForeignCol;
    protected $foreignCol;

    protected $fixed = false;

    public function __construct($nativeMapperClass, $field, $foreignMapperClass, $throughField = null)
    {
        $this->nativeMapperClass = $nativeMapperClass;
        $this->field = $field;
        $this->foreignMapperClass = $foreignMapperClass;
        $this->throughField = $throughField;
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

    protected function fix(Atlas $atlas)
    {
        if ($this->fixed) {
            return;
        }
        $this->fixNativeCol($atlas);
        $this->fixThroughNativeCol($atlas);
        $this->fixThroughForeignCol($atlas);
        $this->fixForeignCol($atlas);
        $this->fixed = true;
    }

    protected function fixNativeCol(Atlas $atlas)
    {
        if ($this->nativeCol) {
            return;
        }

        $nativeMapper = $atlas->mapper($this->nativeMapperClass);
        $this->nativeCol = $nativeMapper->getTable()->getPrimary();
    }

    protected function fixForeignCol(Atlas $atlas)
    {
        if ($this->foreignCol) {
            return;
        }

        $nativeMapper = $atlas->mapper($this->nativeMapperClass);
        $this->foreignCol = $nativeMapper->getTable()->getPrimary();
    }

    protected function fixThroughNativeCol(Atlas $atlas)
    {
    }

    protected function fixThroughForeignCol(Atlas $atlas)
    {
    }

    abstract public function stitchIntoRecord(
        Atlas $atlas,
        Record $record,
        callable $custom = null
    );

    abstract public function stitchIntoRecordSet(
        Atlas $atlas,
        RecordSet $recordSet,
        callable $custom = null
    );
}
