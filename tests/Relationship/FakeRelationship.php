<?php
namespace Atlas\Orm\Relationship;

use Atlas\Orm\Mapper\RecordInterface;
use SplObjectStorage;

class FakeRelationship extends AbstractRelationship
{
    public function __construct()
    {
    }

    public function __call($func, $args)
    {
        return $this->$func(...$args);
    }

    protected function stitchIntoRecord(
        RecordInterface $nativeRecord,
        array $foreignRecords
    ) : void {
        return;
    }

    public function persistForeign(
        RecordInterface $nativeRecord,
        SplObjectStorage $tracker
    ) : void {
        return;
    }
}
