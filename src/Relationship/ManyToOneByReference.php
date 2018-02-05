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
    /**
     *
     * The name of the reference (discriminator) column on the native table.
     *
     * @var string
     *
     */
    protected $referenceCol;

    /**
     *
     * An array of ManyToOne relationship objects, keyed by reference column
     * values.
     *
     * @var array
     *
     */
    protected $relationships = [];

    /**
     *
     * Constructor.
     *
     * @param string $name The related field name to use for this relationship.
     *
     * @param MapperLocator $mapperLocator The MapperLocator with all Mapper
     * objects.
     *
     * @param string $nativeMapperClass The native Mapper class.
     *
     * @param string $referenceCol The name of the reference (discriminator)
     * column on the native table.
     *
     */
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

    /**
     * @inheritdoc
     */
    public function on(array $on) : RelationshipInterface
    {
        throw Exception::invalidReferenceMethod(__FUNCTION__);
    }

    /**
     * @inheritdoc
     */
    public function where(string $cond, ...$bind) : RelationshipInterface
    {
        throw Exception::invalidReferenceMethod(__FUNCTION__);
    }

    /**
     * @inheritdoc
     */
    public function ignoreCase(bool $ignoreCase = true) : AbstractRelationship
    {
        throw Exception::invalidReferenceMethod(__FUNCTION__);
    }

    /**
     * @inheritdoc
     */
    protected function stitchIntoRecord(
        RecordInterface $nativeRecord,
        array $foreignRecords
    ) : void {
        throw Exception::invalidReferenceMethod(__FUNCTION__);
    }

    /**
     *
     * Adds a new reference to a relationship.
     *
     * @param string $referenceVal The value for the reference column on the
     * native table.
     *
     * @param string $foreignMapperClass The foreign mapper class to use for
     * the relationship reference.
     *
     * @param array $on The native => foreign column names.
     *
     * @return self
     *
     */
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

    /**
     *
     * Returns the relationship object for a reference value.
     *
     * @param string $referenceVal
     *
     * @return ManyToOne
     *
     * @throws Exception when there is no relationship for the reference value.
     *
     */
    protected function getReference($referenceVal)
    {
        if (isset($this->relationships[$referenceVal])) {
            return $this->relationships[$referenceVal];
        }

        throw Exception::noSuchReference($this->nativeMapperClass, $referenceVal);
    }

    /**
     *
     * Given an array of native Record objects, stitches the foreign relateds
     * into them as fields under the relationship name.
     *
     * @param array $nativeRecords The native Record objects.
     *
     * @param callable $custom A callable in the form `function (MapperSelect $select)`
     * to modify the foreign MapperSelect statement.
     *
     */
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
     * Given a native Record, sets the related foreign Record values into the
     * native Record; also sets the the native Record reference column value.
     *
     * @param RecordInterface $nativeRecord The native Record to work with.
     *
     */
    public function fixNativeRecordKeys(RecordInterface $nativeRecord) : void
    {
        $this->fixNativeReferenceVal($nativeRecord);
        $relationship = $this->getReference($nativeRecord->{$this->referenceCol});
        $relationship->fixNativeRecordKeys($nativeRecord);
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
        $this->fixNativeReferenceVal($nativeRecord);
        $relationship = $this->getReference($nativeRecord->{$this->referenceCol});
        $relationship->persistForeign($nativeRecord, $tracker);
    }

    /**
     *
     * Given a native record, sets the reference column value from the related
     * foreign record (when present); leaves the value as-is if the foreign
     * record has no known relationship reference.
     *
     * @param RecordInterface $nativeRecord The native record.
     *
     */
    protected function fixNativeReferenceVal(RecordInterface $nativeRecord) : void
    {
        $foreignRecord = $nativeRecord->{$this->name};
        if (! $foreignRecord instanceof RecordInterface) {
            return;
        }

        $foreignRecordMapperClass = $foreignRecord->getMapperClass();
        foreach ($this->relationships as $referenceVal => $relationship) {
            if ($foreignRecordMapperClass == $relationship->foreignMapperClass) {
                $nativeRecord->{$this->referenceCol} = $referenceVal;
                return;
            }
        }
    }
}
