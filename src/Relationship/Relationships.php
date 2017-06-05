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
use Atlas\Orm\Mapper\MapperLocator;
use Atlas\Orm\Mapper\RecordInterface;
use SplObjectStorage;

/**
 *
 * The defined relationships between Mapper objects.
 *
 * @package atlas/orm
 *
 */
class Relationships
{
    /**
     *
     * The locator with all Mapper objects.
     *
     * @var MapperLocator
     *
     */
    protected $mapperLocator;

    /**
     *
     * The various relationship definition objects.
     *
     * @array
     *
     */
    protected $relationships = [];

    /**
     *
     * The fields for the Related to be populated by these Relationships.
     *
     * @var array
     *
     */
    protected $fields = [];

    /**
     *
     * Persist these relateds before the native Record.
     *
     * @var array
     *
     */
    protected $persistBeforeNative = [];

    /**
     *
     * Persist these relateds after the native Record.
     *
     * @var array
     *
     */
    protected $persistAfterNative = [];

    /**
     *
     * Constructor.
     *
     * @param MapperLocator The locator with all Mapper objects.
     *
     */
    public function __construct(MapperLocator $mapperLocator)
    {
        $this->mapperLocator = $mapperLocator;
    }

    /**
     *
     * Defines a one-to-one relationship between Mapper objects.
     *
     * @param string $name The Related field name.
     *
     * @param string $nativeMapperClass The native Mapper class name.
     *
     * @param string $foreignMapperClass The foreign Mapper class name.
     *
     * @return RelationshipInterface
     *
     */
    public function oneToOne(
        $name,
        $nativeMapperClass,
        $foreignMapperClass
    ) {
        return $this->set(
            $name,
            OneToOne::CLASS,
            $nativeMapperClass,
            $foreignMapperClass,
            'persistAfterNative'
        );
    }

    /**
     *
     * Defines a one-to-many relationship between Mapper objects.
     *
     * @param string $name The Related field name.
     *
     * @param string $nativeMapperClass The native Mapper class name.
     *
     * @param string $foreignMapperClass The foreign Mapper class name.
     *
     * @return RelationshipInterface
     *
     */
    public function oneToMany(
        $name,
        $nativeMapperClass,
        $foreignMapperClass
    ) {
        return $this->set(
            $name,
            OneToMany::CLASS,
            $nativeMapperClass,
            $foreignMapperClass,
            'persistAfterNative'
        );
    }

    /**
     *
     * Defines a many-to-one relationship between Mapper objects.
     *
     * @param string $name The Related field name.
     *
     * @param string $nativeMapperClass The native Mapper class name.
     *
     * @param string $foreignMapperClass The foreign Mapper class name.
     *
     * @return RelationshipInterface
     *
     */
    public function manyToOne(
        $name,
        $nativeMapperClass,
        $foreignMapperClass
    ) {
        return $this->set(
            $name,
            ManyToOne::CLASS,
            $nativeMapperClass,
            $foreignMapperClass,
            'persistBeforeNative'
        );
    }

    /**
     *
     * Defines a many-to-many relationship between Mapper objects.
     *
     * @param string $name The Related field name.
     *
     * @param string $nativeMapperClass The native Mapper class name.
     *
     * @param string $foreignMapperClass The foreign Mapper class name.
     *
     * @param string $throughName The name of the Related field that holds
     * the association table (join table) values.
     *
     * @return RelationshipInterface
     *
     */
    public function manyToMany(
        $name,
        $nativeMapperClass,
        $foreignMapperClass,
        $throughName
    ) {
        return $this->set(
            $name,
            ManyToMany::CLASS,
            $nativeMapperClass,
            $foreignMapperClass,
            'persistBeforeNative',
            $throughName
        );
    }

    /**
     *
     * Returns a relationship object by field name.
     *
     * @param string $name The related field name.
     *
     * @return RelationshipInterface
     *
     */
    public function get($name)
    {
        return $this->relationships[$name];
    }

    /**
     *
     * Returns the array of fields for the Related that will be populated by
     * these Relationships.
     *
     * @return array
     *
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     *
     * Given an array of native Record objects, stitches the specified foreign
     * Relationship results into them.
     *
     * @param array $nativeRecords The native Record objects.
     *
     * @param array $with Stitch these named relationship results into the
     * native Record objects.
     *
     */
    public function stitchIntoRecords(
        array $nativeRecords,
        array $with = []
    ) {
        foreach ($this->fixWith($with) as $name => $custom) {
            if (! isset($this->relationships[$name])) {
                throw Exception::relationshipDoesNotExist($name);
            }
            $this->relationships[$name]->stitchIntoRecords(
                $nativeRecords,
                $custom
            );
        }
    }

    /**
     *
     * Sets a relationship definition.
     *
     * @param string $name The Related field name.
     *
     * @param string $relationshipClass The relationship class name.
     *
     * @param string $nativeMapperClass The native Mapper class name.
     *
     * @param string $foreignMapperClass The foreign Mapper class name.
     *
     * @param string $persistencePriority The persistence priority property
     * name.
     *
     * @param string $throughName The name of the Related field that holds
     * the association table (join table) values.
     *
     * @return RelationshipInterface
     *
     */
    protected function set(
        $name,
        $relationshipClass,
        $nativeMapperClass,
        $foreignMapperClass,
        $persistencePriority,
        $throughName = null
    ) {
        if (! class_exists($foreignMapperClass)) {
            throw Exception::classDoesNotExist($foreignMapperClass);
        }

        if ($throughName && ! isset($this->relationships[$throughName])) {
            throw Exception::relationshipDoesNotExist($throughName);
        }

        $this->fields[$name] = null;

        $relationship = $this->newRelationship(
            $relationshipClass,
            $name,
            $nativeMapperClass,
            $foreignMapperClass,
            $throughName
        );

        $this->{$persistencePriority}[] = $relationship;
        $this->relationships[$name] = $relationship;
        return $relationship;
    }

    /**
     *
     * Returns a new relationship definition object.
     *
     * @param string $relationshipClass The relationship class name.
     *
     * @param string $name The Related field name.
     *
     * @param string $nativeMapperClass The native Mapper class name.
     *
     * @param string $foreignMapperClass The foreign Mapper class name.
     *
     * @param string $throughName The name of the Related field that holds
     * the association table (join table) values.
     *
     * @return RelationshipInterface
     *
     */
    protected function newRelationship(
        $relationshipClass,
        $name,
        $nativeMapperClass,
        $foreignMapperClass,
        $throughName = null
    ) {
        return new $relationshipClass(
            $name,
            $this->mapperLocator,
            $nativeMapperClass,
            $foreignMapperClass,
            $throughName
        );
    }

    /**
     *
     * Normalizes a `$with` specification.
     *
     * @param array $spec The `$with` specification.
     *
     * @return array
     *
     */
    protected function fixWith(array $spec)
    {
        $with = [];
        foreach ($spec as $key => $val) {
            if (is_int($key)) {
                $with[$val] = null;
            } elseif (is_array($val) && ! is_callable($val)) {
                $with[$key] = function ($select) use ($val) {
                    $select->with($val);
                };
            } else {
                $with[$key] = $val;
            }
        }
        return $with;
    }

    /**
     *
     * Given a native Record, sets the related foreign Record values into the
     * native Record.
     *
     * @param RecordInterface $nativeRecord The native Record to work with.
     *
     */
    public function fixNativeRecordKeys(RecordInterface $nativeRecord)
    {
        foreach ($this->relationships as $relationship) {
            $relationship->fixNativeRecordKeys($nativeRecord);
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
    public function fixForeignRecordKeys(RecordInterface $nativeRecord)
    {
        foreach ($this->relationships as $relationship) {
            $relationship->fixForeignRecordKeys($nativeRecord);
        }
    }

    /**
     *
     * Persist all the relateds that go before a native Record.
     *
     * @param RecordInterface $nativeRecord The native Record being persisted.
     *
     * @param SplObjectStorage $tracker Tracks which Record objects have been
     * operated on, to prevent infinite recursion.
     *
     */
    public function persistBeforeNative(RecordInterface $nativeRecord, SplObjectStorage $tracker)
    {
        foreach ($this->persistBeforeNative as $relationship) {
            $relationship->persistForeign($nativeRecord, $tracker);
        }
    }

    /**
     *
     * Persist all the relateds that go after a native Record.
     *
     * @param RecordInterface $nativeRecord The native Record being persisted.
     *
     * @param SplObjectStorage $tracker Tracks which Record objects have been
     * operated on, to prevent infinite recursion.
     *
     */
    public function persistAfterNative(RecordInterface $nativeRecord, SplObjectStorage $tracker)
    {
        foreach ($this->persistAfterNative as $relationship) {
            $relationship->persistForeign($nativeRecord, $tracker);
        }
    }
}
