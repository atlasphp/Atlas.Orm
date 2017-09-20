<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Orm\Table;

use Atlas\Orm\Exception;
use SplObjectStorage;

/**
 *
 * A Table-specific identity map for Row objects.
 *
 * @package atlas/orm
 *
 */
class IdentityMap
{
    /**
     *
     * Map of serialized identities to Row objects.
     *
     * @var array
     *
     */
    protected $serialToRow = [];

    /**
     *
     * Map of Row objects to serialized identities.
     *
     * @var SplObjectStorage
     *
     */
    protected $rowToSerial;

    /**
     *
     * Initial values in Row objects; use for difference comparisons.
     *
     * @var SplObjectStorage
     *
     */
    protected $initial;

    /**
     *
     * Constructor.
     *
     */
    public function __construct()
    {
        $this->rowToSerial = new SplObjectStorage();
        $this->initial = new SplObjectStorage();
    }

    /**
     *
     * Sets a Row into the identity map.
     *
     * @param RowInterface $row The Row object.
     *
     * @param array $initial The initial values on the Row.
     *
     * @param array $primaryKey The columns in the primary key.
     *
     */
    public function setRow(RowInterface $row, array $initial, array $primaryKey) : void
    {
        if ($this->hasRow($row)) {
            throw Exception::rowAlreadyMapped();
        }

        $primary = [];
        foreach ($primaryKey as $primaryCol) {
            $primary[$primaryCol] = $row->$primaryCol;
        }

        $serial = $this->getSerial($primary);
        $this->serialToRow[$serial] = $row;
        $this->rowToSerial[$row] = $serial;
        $this->initial[$row] = $initial;
    }

    /**
     *
     * Does a Row already exist in the map?
     *
     * @param RowInterface $row The Row to look for.
     *
     * @return boolean
     *
     */
    public function hasRow(RowInterface $row) : bool
    {
        return isset($this->rowToSerial[$row]);
    }

    /**
     *
     * Returns a mapped Row by its primary-key value.
     *
     * @param array $primary Primary-key column-value pairs.
     *
     * @return ?RowInterface The mapped Row on success, or null on failure.
     *
     */
    public function getRow(array $primary) : ?RowInterface
    {
        $serial = $this->getSerial($primary);
        if (! isset($this->serialToRow[$serial])) {
            return null;
        }

        return $this->serialToRow[$serial];
    }

    /**
     *
     * This is a ghetto hack to serialize a composite primary key to a string,
     * so it can be used for array key lookups. It works just as well for
     * single-value keys as well.
     *
     * All it does it implode() the primary values with a pipe (to make it
     * easier for people to see the separator) and an ASCII "unit separator"
     * character (to include something that is unlikely to be used in a real
     * primary-key value, and thus help prevent the serial string from being
     * subverted).
     *
     * WARNING: You should sanitize your primary-key values to disallow ASCII
     * character 31 (hex 1F) to keep the lookup working properly. This is only
     * a problem with non-integer keys.
     *
     * WARNING: Null, false, and empty-string key values are treated as
     * identical by this algorithm. That means these values are interchangeable
     * and are not differentiated. You should sanitize your primary-key values
     * to disallow null, false, and empty-string values. This is only a problem
     * with non-integer keys.
     *
     * WARNING: The serial string version of the primary key depends on the
     * values always being in the same order. E.g., `['foo' => 1, 'bar' => 2]`
     * will result in a different serial than `['bar' => 2, 'foo' => 1]`, even
     * though the key-value pairs themselves are the same.
     *
     * @param array $primary Primary-key column-value pairs.
     *
     * @return string The serialized identity value.
     *
     */
    public function getSerial(array $primary) : string
    {
        $sep = "|\x1F"; // a pipe, and ASCII 31 ("unit separator")
        return $sep . implode($sep, $primary). $sep;
    }

    /**
     *
     * Resets the initial values for a Row to its current values.
     *
     * @param RowInterface $row Reset the initial values for this Row.
     *
     * @throws Exception when the Row is not mapped.
     *
     */
    public function resetInitial(RowInterface $row) : void
    {
        if (! $this->hasRow($row)) {
            throw Exception::rowNotMapped();
        }

        $this->initial[$row] = $row->getArrayCopy();
    }

    /**
     *
     * Gets the initial values for Row.
     *
     * @param RowInterface $row The Row to get initial values for.
     *
     * @return array The array of initial values.
     *
     * @throws Exception when the Row is not mapped.
     *
     */
    public function getInitial(RowInterface $row) : array
    {
        if (! $this->hasRow($row)) {
            throw Exception::rowNotMapped();
        }

        return $this->initial[$row];
    }
}
