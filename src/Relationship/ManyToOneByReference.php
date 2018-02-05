<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Orm\Relationship;

use Atlas\Orm\Exception;
use Atlas\Orm\Mapper\RecordInterface;
use Atlas\Orm\Mapper\MapperLocator;
use SplObjectStorage;

/**
 *
 * Defines a many-to-one relationship via a reference column value.
 *
 * Also known as "polymorphic association" (though that is an OOP term and not
 * an SQL term).
 *
 * The use of the word "reference" is lifted from Postgres; cf.
 * <https://www.postgresql.org/docs/9.4/static/sql-createtable.html> (search
 * for "REFERENCES").
 *
 * @package atlas/orm
 *
 */
class ManyToOneByReference extends AbstractRelationship
{
    protected $referenceCol;

    protected $relationships = [];

    public function __construct(
        string $name,
        MapperLocator $mapperLocator,
        string $nativeMapperClass,
        string $referenceCol
    ) {
        $this->name = $name;
        $this->mapperLocator = $mapperLocator;
        $this->nativeMapperClass = $nativeMapperClass;
        $this->referenceCol = $referenceCol;
    }

    public function on(array $on) : RelationshipInterface
    {
        throw Exception::invalidReferenceMethod(__FUNCTION__);
    }

    public function where(string $cond, ...$bind) : RelationshipInterface
    {
        throw Exception::invalidReferenceMethod(__FUNCTION__);
    }

    public function ignoreCase(bool $ignoreCase = true) : AbstractRelationship
    {
        throw Exception::invalidReferenceMethod(__FUNCTION__);
    }

    protected function stitchIntoRecord(
        RecordInterface $nativeRecord,
        array $foreignRecords
    ) : void {
        throw Exception::invalidReferenceMethod(__FUNCTION__);
    }

    public function to(
        string $referenceVal,
        string $foreignMapperClass,
        array $on
    ) : self {
        $relationship = new ManyToOne(
            $this->name,
            $this->mapperLocator,
            $this->nativeMapperClass,
            $foreignMapperClass
        );
        $this->relationships[$referenceVal] = $relationship->on($on);
        return $this;
    }

    protected function getReference($referenceVal)
    {
        if (isset($this->relationships[$referenceVal])) {
            return $this->relationships[$referenceVal];
        }

        throw Exception::noSuchReference($this->nativeMapperClass, $referenceVal);
    }

    public function stitchIntoRecords(
        array $nativeRecords,
        callable $custom = null
    ) : void {
        if (! $nativeRecords) {
            return;
        }

        $nativeSubsets = [];
        foreach ($nativeRecords as $nativeRecord) {
            $nativeSubsets[$nativeRecord->{$this->referenceCol}][] = $nativeRecord;
        }

        foreach ($nativeSubsets as $referenceVal => $nativeSubset) {
            $reference = $this->getReference($referenceVal);
            $reference->stitchIntoRecords($nativeSubset, $custom);
        }
    }

    /**
     *
     * Given a native Record, sets the appropriate native Record values into all
     * related foreign Records.
     *
     * @param RecordInterface $nativeRecord The native Record to work with.
     *
     */
    public function fixForeignRecordKeys(RecordInterface $nativeRecord) : void
    {
        $reference = $this->getReference($nativeRecord->{$this->referenceCol});
        $reference->fixForeignRecordKeys($nativeRecord);
    }

    /**
     *
     * Given a native Record, persists the related foreign Records.
     *
     * @param RecordInterface $nativeRecord The native Record being persisted.
     *
     * @param SplObjectStorage $tracker Tracks which Record objects have been
     * operated on, to prevent infinite recursion.
     *
     */
    public function persistForeign(RecordInterface $nativeRecord, SplObjectStorage $tracker) : void
    {
        $foreignRecord = $nativeRecord->{$this->name};
        if (! $foreignRecord instanceof RecordInterface) {
            return;
        }

        if (! isset($nativeRecord->{$this->referenceCol})) {
            $this->setNativeReferenceVal($nativeRecord, $foreignRecord);
        }

        $relationship = $this->getReference($nativeRecord->{$this->referenceCol});
        $relationship->persistForeignRecord($nativeRecord, $tracker);
    }

    protected function setNativeReferenceVal(
        RecordInterface $nativeRecord,
        RecordInterface $foreignRecord
    ) : void {
        $foreignRecordMapperClass = $foreignRecord->getMapperClass();
        foreach ($this->relationships as $referenceVal => $relationship) {
            if ($foreignRecordMapperClass == $relationship->foreignMapperClass) {
                $nativeRecord->{$this->referenceCol} = $referenceVal;
                return;
            }
        }
    }
}
