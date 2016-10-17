<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Orm\Relationship;

use Atlas\Orm\Mapper\MapperLocator;
use Atlas\Orm\Mapper\MapperSelect;
use Atlas\Orm\Mapper\RecordInterface;
use Atlas\Orm\Mapper\RecordSetInterface;

/**
 *
 * Defines a relationship between Mapper objects.
 *
 * @package atlas/orm
 *
 */
abstract class AbstractRelationship implements RelationshipInterface
{
    /**
     *
     * MapperLocator for all Mapper objects.
     *
     * @var MapperLocator
     *
     */
    protected $mapperLocator;

    /**
     *
     * The name of this relationship, to be used as a field name on a Related.
     *
     * @var string
     *
     */
    protected $name;

    /**
     *
     * The native Mapper class in the relationship.
     *
     * @var string
     *
     */
    protected $nativeMapperClass;

    /**
     *
     * The native Mapper instance in the relationship.
     *
     * @var MapperInterface
     *
     */
    protected $nativeMapper;

    /**
     *
     * The foreign Mapper class in the relationship.
     *
     * @var string
     *
     */
    protected $foreignMapperClass;

    /**
     *
     * The foreign Mapper instance in the relationship.
     *
     * @var MapperInterface
     *
     */
    protected $foreignMapper;

    /**
     *
     * The table name used by the foreign Mapper.
     *
     * @var string
     *
     */
    protected $foreignTableName;

    /**
     *
     * The relationship is on these native => foreign column names.
     *
     * @var array
     *
     */
    protected $on = [];

    /**
     *
     * In a many-to-many relationship, the name of the related field that holds
     * the association table (join table) values.
     *
     * @var string
     *
     */
    protected $throughName;

    /**
     *
     * Have all relationship properties been initialized?
     *
     * @var bool
     *
     */
    protected $initialized = false;

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
     * @param string $foreignMapperClass The foreign Mapper class.
     *
     * @param string $throughName The name of the related field that holds the
     * association table (join table) values for a many-to-many relationship.
     *
     */
    public function __construct(
        $name,
        MapperLocator $mapperLocator,
        $nativeMapperClass,
        $foreignMapperClass,
        $throughName = null
    ) {
        $this->name = $name;
        $this->mapperLocator = $mapperLocator;
        $this->nativeMapperClass = $nativeMapperClass;
        $this->foreignMapperClass = $foreignMapperClass;
        $this->throughName = $throughName;
    }

    /**
     *
     * Returns the settings properties for this relationship.
     *
     * @return array
     *
     */
    public function getSettings()
    {
        $this->initialize();
        $settings = get_object_vars($this);
        unset($settings['initialized']);
        unset($settings['mapperLocator']);
        unset($settings['nativeMapper']);
        unset($settings['foreignMapper']);
        return $settings;
    }

    /**
     *
     * Sets the native => foreign relationship column names.
     *
     * @param array
     *
     */
    public function on(array $on)
    {
        $this->on = $on;
    }

    /**
     *
     * Returns the native => foreign column names.
     *
     * @return array
     *
     */
    public function getOn()
    {
        $this->initialize();
        return $this->on;
    }

    /**
     *
     * Returns the foreign Mapper instance.
     *
     * @return MapperInterface
     *
     */
    public function getForeignMapper()
    {
        $this->initialize();
        return $this->foreignMapper;
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
    ) {
        if (! $nativeRecords) {
            return;
        }

        $this->initialize();

        $foreignRecords = $this->fetchForeignRecords($nativeRecords, $custom);
        foreach ($nativeRecords as $nativeRecord) {
            $this->stitchIntoRecord($nativeRecord, $foreignRecords);
        }
    }

    /**
     *
     * Initializes all the relationship settings.
     *
     */
    protected function initialize()
    {
        if ($this->initialized) {
            return;
        }

        $this->nativeMapper = $this->mapperLocator->get($this->nativeMapperClass);
        $this->foreignMapper = $this->mapperLocator->get($this->foreignMapperClass);
        $this->foreignTableName = $this->foreignMapper->getTable()->getName();

        if (! $this->on) {
            $this->initializeOn();
        }

        $this->initialized = true;
    }

    /**
     *
     * Initializes the `$on` property for the relationship.
     *
     */
    protected function initializeOn()
    {
        foreach ($this->nativeMapper->getTable()->getPrimaryKey() as $col) {
            $this->on[$col] = $col;
        }
    }

    /**
     *
     * Fetches an array of related foreign Record objects.
     *
     * @param array $records The Record objects for which to fetch the foreign
     * Record objects.
     *
     * @param null|callable When present, a callable to modify the MapperSelect
     * for the foreign fetch.
     *
     * @return array
     *
     */
    protected function fetchForeignRecords(array $records, $custom)
    {
        $select = $this->foreignSelect($records);
        if ($custom) {
            $custom($select);
        }
        return $select->fetchRecords();
    }

    /**
     *
     * Returns the MapperSelect object for the foreign fetch.
     *
     * @param array $records The Record objects for which to fetch the foreign
     * Record objects.
     *
     * @return MapperSelect
     *
     */
    protected function foreignSelect(array $records)
    {
        $select = $this->foreignMapper->select();

        if (count($this->on) > 1) {
            $this->foreignSelectComposite($select, $records);
            return $select;
        }

        $this->foreignSelectSimple($select, $records);
        return $select;
    }

    /**
     *
     * Modifies a MapperSelect to fetch records on a simple key relationship.
     *
     * @param MapperSelect $select The foreign MapperSelect.
     *
     * @param array $records The Record objects for which to fetch the foreign
     * Record objects.
     *
     */
    protected function foreignSelectSimple(MapperSelect $select, array $records)
    {
        $vals = [];
        $nativeCol = key($this->on);
        foreach ($records as $record) {
            $row = $record->getRow();
            $vals[] = $row->$nativeCol;
        }

        $foreignCol = current($this->on);
        $where = "{$this->foreignTableName}.{$foreignCol} IN (?)";
        $select->where($where, array_unique($vals));
    }

    /**
     *
     * Modifies a MapperSelect to fetch records on a composite key relationship.
     *
     * @param MapperSelect $select The foreign MapperSelect.
     *
     * @param array $records The Record objects for which to fetch the foreign
     * Record objects.
     *
     */
    protected function foreignSelectComposite(MapperSelect $select, array $records)
    {
        $uniques = $this->getUniqueCompositeKeys($records);
        $cond = '(' . implode(' = ? AND ', $this->on) . '= ?)';

        // get the first unique composite
        $firstUnique = array_shift($uniques);
        if (! $uniques) {
            // there are no uniques left, which means this is the only one.
            // no need to wrap in parens.
            $select->where($cond, ...$firstUnique);
            return;
        }

        // multiple unique conditions. retain the last unique for later.
        $lastUnique = array_pop($uniques);

        // prefix the first unique with "AND ( -- composite keys" to keep all
        // the uniques within parens
        $select->where(
            '( -- composite keys' . PHP_EOL . '    ' . $cond,
            ...$firstUnique
        );

        // OR the middle uniques within the parens
        foreach ($uniques as $middleUnique) {
            $select->orWhere($cond, ...$middleUnique);
        }

        // suffix the last unique with ") -- composite keys" to end the parens
        $select->orWhere(
            $cond . PHP_EOL . '    ) -- composite keys',
            ...$lastUnique
        );
    }

    /**
     *
     * Given an array of Record objects, finds the unique composite key
     * combinations with them.
     *
     * @param array $records The Record objects for which to fetch the foreign
     * Record objects.
     *
     * @return array
     *
     */
    protected function getUniqueCompositeKeys(array $records)
    {
        $uniques = [];
        foreach ($records as $record) {
            $row = $record->getRow();
            $vals = [];
            foreach ($this->on as $nativeCol => $foreignCol) {
                $vals[] = $row->$nativeCol;
            }
            // a pipe, and ASCII 31 ("unit separator").
            // identical composite values should have identical array keys.
            $key = implode("|\x1F", $vals);
            $uniques[$key] = $vals;
        }
        return $uniques;
    }

    /**
     *
     * Do two Record objects match on their relationship keys?
     *
     * @param RecordInterface $nativeRecord The native Record.
     *
     * @param RecordInterface $foreignRecord The foreign Record.
     *
     * @return bool
     *
     */
    protected function recordsMatch(
        RecordInterface $nativeRecord,
        RecordInterface $foreignRecord
    ) {
        $nativeRow = $nativeRecord->getRow();
        $foreignRow = $foreignRecord->getRow();
        foreach ($this->on as $nativeCol => $foreignCol) {
            if ($nativeRow->$nativeCol != $foreignRow->$foreignCol) {
                return false;
            }
        }
        return true;
    }

    /**
     *
     * Stitches one or more foreign Record objects into a native Record.
     *
     * @param RecordInterface $nativeRecord The native Record.
     *
     * @param array $foreignRecords All the foreign Record objects fetched for
     * the relationship.
     *
     */
    abstract protected function stitchIntoRecord(
        RecordInterface $nativeRecord,
        array $foreignRecords
    );
}
