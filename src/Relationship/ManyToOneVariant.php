<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Orm\Relationship;

use Atlas\Orm\Mapper\RecordInterface;
use Atlas\Orm\Mapper\MapperLocator;
use SplObjectStorage;

/**
 *
 * Defines a many-to-one variant relationship.
 *
 * @package atlas/orm
 *
 */
class ManyToOneVariant extends AbstractRelationship
{
    protected $variantCol;

    protected $variants = [];

    public function __construct(
        string $name,
        MapperLocator $mapperLocator,
        string $nativeMapperClass,
        string $variantCol
    ) {
        $this->name = $name;
        $this->mapperLocator = $mapperLocator;
        $this->nativeMapperClass = $nativeMapperClass;
        $this->variantCol = $variantCol;
    }

    public function on(array $on) : RelationshipInterface
    {
        throw Exception::invalidVariantMethod(__FUNCTION__);
    }

    public function where(string $cond, ...$bind) : RelationshipInterface
    {
        throw Exception::invalidVariantMethod(__FUNCTION__);
    }

    public function ignoreCase(bool $ignoreCase = true) : AbstractRelationship
    {
        // later, apply to all variants; meanwhile:
        throw Exception::invalidVariantMethod(__FUNCTION__);
    }

    protected function stitchIntoRecord(
        RecordInterface $nativeRecord,
        array $foreignRecords
    ) : void {
        throw Exception::invalidVariantMethod(__FUNCTION__);
    }

    public function variant(
        string $variantVal,
        string $foreignMapperClass,
        array $on
    ) : self {
        $relationship = new ManyToOne(
            $this->name,
            $this->mapperLocator,
            $this->nativeMapperClass,
            $foreignMapperClass
        );
        $this->variants[$variantVal] = $relationship->on($on);
        return $this;
    }

    protected function getVariant($variantVal)
    {
        if (isset($this->variants[$variantVal])) {
            return $this->variants[$variantVal];
        }

        throw Exception::noSuchVariant($this->nativeMapperClass, $variantVal);
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
            $nativeSubsets[$nativeRecord->{$this->variantCol}][] = $nativeRecord;
        }

        foreach ($nativeSubsets as $variantVal => $nativeSubset) {
            $variant = $this->getVariant($variantVal);
            $variant->stitchIntoRecords($nativeSubset, $custom);
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
        $variant = $this->getVariant($nativeRecord->{$this->variantCol});
        $variant->fixForeignRecordKeys($nativeRecord);
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
        $variant = $this->getVariant($nativeRecord->{$this->variantCol});
        $variant->persistForeignRecord($nativeRecord, $tracker);
    }
}
