<?php
namespace Atlas\Relation;

use Atlas\Atlas;

// the Relation should let you specify what to do when there are no
// related records. $rel->nullWhenEmpty(), arrayWhenEmpty(), newRecordWhenEmpty(),
// newRecordSetWhenEmpty()? or should it be a $rel->default() to indicate the
// default value? maybe Table should return "false" instead of "null" when no
// row is found.

abstract class AbstractRelationshipship
{
    protected $field;

    protected $foreignRecordClass;
    protected $foreignMapper;

    protected $nativeCol;
    protected $throughNativeCol;
    protected $throughForeignCol;
    protected $foreignCol;

    protected $nativeField; // the has-many collection field on the native record
    protected $throughField; // the foreign field in the through collection

    protected $fixed = false;

    public function __construct($nativeMapper, $field, $foreignRecordClass)
    {
        $this->nativeMapper = $nativeMapper;
        $this->field = $field;
        $this->foreignRecordClass = $foreignRecordClass;
    }

    public function nativeCol($nativeCol)
    {
        $this->nativeCol = $nativeCol;
    }

    public function foreignCol($foreignCol)
    {
        $this->foreignCol = $foreignCol;
    }

    protected function fix(Atlas $atlas)
    {
        if ($this->fixed) {
            return;
        }

        $this->fixForeignMapper($atlas);

        $this->fixNativeCol();
        $this->fixThroughNativeCol();
        $this->fixThroughForeignCol();
        $this->fixForeignCol();

        $this->fixed = true;
    }

    protected function fixForeignMapper(Atlas $atlas)
    {
        $this->foreignMapper = $atlas->mapper($this->foreignRecordClass);
    }

    protected function fixNativeCol()
    {
        if (! $this->nativeCol) {
            $this->nativeCol = $this->nativeMapper->getTable()->getPrimary();
        }
    }

    protected function fixForeignCol()
    {
        if (! $this->foreignCol) {
            $this->foreignCol = $this->nativeMapper->getTable()->getPrimary();
        }
    }

    protected function fixThroughNativeCol()
    {
    }

    protected function fixThroughForeignCol()
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
